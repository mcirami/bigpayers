<?php
namespace LeadMax\TrackYourStats\Clicks;

use LeadMax\TrackYourStats\Database\DatabaseConnection;
use PDO;

use GeoIp2\Database\Reader;


//Begin class
class Click
{
    public $id;
    public $idclicks;
    public $first_timestamp;
    public $rep_idrep;
    public $offer_idoffer;
    public $ip_address;
    public $country_code;
    public $referer;
    public $browser_agent;
    public $click_type;
    public $last_clickid;

    public $subVarArray;
    public $queryString;


    public $clickData = array();

    const TYPE_UNIQUE = 0;
    const TYPE_RAW = 1;
    const TYPE_BLACKLISTED = 2;
    const TYPE_GENERATED = 3;


    public function __construct($id = false)
    {
        if ($id) {
            $this->id = $id;
            $this->clickData = self::querySelectOne($id);
        }
    }

    public static function updateClickType($click_id, $click_type)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE clicks SET click_type = :click_type WHERE idclicks = :click_id";
        $prep = $db->prepare($sql);
        $prep->bindParam("click_type", $click_type);
        $prep->bindParam(":click_id", $click_id);

        return $prep->execute();
    }


    public function __get($name)
    {
        if (isset($this->clickData[$name])) {
            return $this->clickData[$name];
        }
    }


    public function save()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "INSERT INTO clicks(first_timestamp, rep_idrep, offer_idoffer, ip_address, browser_agent, click_type, country_code, referer) VALUES(:timestamp, :user_id, :offer_id, :ip, :browser_agent, :click_type, :country_code, :referer)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":timestamp", $this->first_timestamp);
        $prep->bindParam(":user_id", $this->rep_idrep);
        $prep->bindParam(":offer_id", $this->offer_idoffer);
        $prep->bindParam(":ip", $this->ip_address);
        $prep->bindParam(":browser_agent", $this->browser_agent);
        $prep->bindParam(":click_type", $this->click_type);
        $prep->bindParam(":country_code", $this->country_code);
        $prep->bindParam(":referer", $this->referer);

        if ($prep->execute()) {
            $this->id = $db->lastInsertId();

            $this->saveSubVariables();

			$this->saveSubId();

            $this->saveGeoData();

            return true;
        } else {
            return false;
        }
    }

    private function saveSubVariables()
    {

        if (isset($this->subVarArray) == false) {
            $this->subVarArray = $_GET;
        }

        if (isset($this->queryString) == false) {
            $this->queryString = $_SERVER["REQUEST_URI"];
        }

        $db = DatabaseConnection::getInstance();

        $sql = "INSERT INTO click_vars (click_id, url, sub1, sub2,sub3,sub4,sub5) VALUES ( :click_id, :url, :sub1, :sub2, :sub3, :sub4, :sub5)";

        $stmt = $db->prepare($sql);

        for ($i = 1; $i <= 5; $i++) {
            $sub = isset($this->subVarArray["sub{$i}"]) ? $this->subVarArray["sub{$i}"] : "";
            $stmt->bindValue(":sub{$i}", $sub);
        }

        $stmt->bindParam(":click_id", $this->id);
        $stmt->bindParam(":url", $this->queryString);

        return $stmt->execute();
    }

	private function saveSubId() {
		$sub1 = trim($this->subVarArray['sub1'] ?? '');
		if ($sub1 === '') {
			return false;
		}
		$db = DatabaseConnection::getInstance();
		$sql = "INSERT IGNORE INTO sub_ids (idrep, sub_id) VALUES (:idrep, :sub_id)";
		$stmt = $db->prepare($sql);
		return $stmt->execute([
			':idrep'  => $this->rep_idrep,
			':sub_id' => $this->subVarArray['sub1'],
		]);
	}

    private function saveGeoData()
    {
        $db = DatabaseConnection::getInstance();

        $sql = "INSERT INTO click_geo (click_id, iso_code, postal, ip) VALUES(:clickID, :iso_code, :postal, :ip)";

        $stmt = $db->prepare($sql);


        try {
            // new geoip reader
            $reader = new Reader('resources/GeoIP2-City.mmdb');

            //trys to get their iso code and postal
            $record = $reader->city($this->ip_address);
            $isoCode = $record->country->isoCode;
            $postal = $record->postal->code;

            if ($postal == null) {
                $postal = "UNKNOWN";
            }

            if ($isoCode == null) {
                $isoCode = "UNKNOWN";
            }

            $stmt->bindParam(":iso_code", $isoCode);
            $stmt->bindParam(":postal", $postal);
        } catch (\Exception $e) // if their ip wasnt in the db, set default values
        {
            $u = "UNKNOWN";

            $stmt->bindParam(":iso_code", $u);
            $stmt->bindParam(":postal", $u);
        }

        $stmt->bindParam(":clickID", $this->id);

        $stmt->bindParam(":ip", $this->ip_address);

        //insert into click_geo
        return $stmt->execute();
    }

    // SELECT ONE
    public static function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks WHERE idclicks=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public static function querySelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks WHERE idclicks=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    static function SelectOneByUID($uid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks WHERE uid= :uid ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }


    // SELECT ALL
    static function SelectAll()
    {
        $dbc = new \dboptions();
        $record = $dbc->rawSelect("SELECT * FROM clicks");

        return $record->fetchAll(PDO::FETCH_OBJ);
    }

    // SELECT All 2
    static function select_all()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM clicks ";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }


} // end class
    