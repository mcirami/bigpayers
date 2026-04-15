<?php
/*
* =======================================================================
* CLASSNAME:        offer
* DATE CREATED:      16-05-2017
* FOR TABLE:          offer
* FOR DATA BASE:    trackyourstats
* IMPORTANT:
* the 'sanitize()' keyword is a defined function to prevent sql injection located @ lib/Functions.php
* 'post()' is also defined function located @ lib/funtions.php
* You can further improve these functions if necessary.
* =======================================================================
*/

namespace LeadMax\TrackYourStats\Offer;

use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Privileges;
use PDO;

//Begin class
class Offer
{


    const TYPE_CPA = 0;
    const TYPE_CPC = 1;
    const TYPE_BLACKLISTED = 2;
    const TYPE_PENDING_CONVERSION = 3;

    CONST VISIBILITY_PRIVATE = 0;
    const VISIBILITY_PUBLIC = 1;
    CONST VISIBILITY_REQUESTABLE = 2;

    public $idoffer;
    public $offer_name;
    public $description;
    public $url;
    public $payout;
    public $status;
    public $offer_timestamp;
    // Table Columns
    //(idoffer,offer_name,description,url,payout,status,offer_timestamp)
    // Table Prepare Columns
    //(:idoffer,:offer_name,:description,:url,:payout,:status,:offer_timestamp)

    //Constructor
    public function __construct(
        $idoffer = '',
        $offer_name = '',
        $description = '',
        $url = '',
        $payout = '',
        $status = '',
        $offer_timestamp = ''
    ) {
        $this->idoffer = $idoffer;
        $this->offer_name = $offer_name;
        $this->description = $description;
        $this->url = $url;
        $this->payout = $payout;
        $this->status = $status;
        $this->offer_timestamp = $offer_timestamp;
    }

    public static function offerTypeAsString($offerType)
    {
        switch ($offerType) {
            case static::TYPE_CPA:
                return "CPA";

            case static::TYPE_CPC:
                return "CPC";

            case static::TYPE_PENDING_CONVERSION:
                return "SALE LOG";

            default:
                return "UNKNOWN";

        }
    }


    public static function deleteOffer($offer_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE offer SET status = 2 WHERE idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":offer_id", $offer_id);

        return $prep->execute();
    }

    // SELECT getCount
    static function getCount($active)
    {
        $dbc = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "";
        if ($active == 1) {
            $sql = "SELECT * FROM offer INNER JOIN rep_h_offer ON rep_has_offer.rep_idrep = :repid AND rep_has_offer.offer_idoffer = offer.idoffer WHERE offer.status = 1 ";
        } else {
            $sql = "SELECT * FROM offer  INNER JOIN rep_has_offer ON rep_has_offer.rep_idrep = :repid AND rep_has_offer.offer_idoffer = offer.idoffer WHERE offer.status = 0 ";
        }

        $new_privileges = new Privileges();
        $repType = Session::userType();
        if ($repType == \App\Privilege::ROLE_GOD) {
            $sql = "SELECT
                        offer.idoffer,
                        offer.offer_name,
                        offer.description,
                        offer.url,
                        offer.payout,
                        offer.status,
                        offer.offer_timestamp
                    FROM
                        offer ";
            if ($active == 1) {
                $sql .= " WHERE status = 1 ";
            } else {
                $sql .= " WHERE status = 0 ";
            }


        }


        $prep = $dbc->prepare($sql);
        if ($repType != \App\Privilege::ROLE_GOD) {
            $userID = Session::userID();
            $prep->bindParam(":repid", $userID);
        }

        $prep->execute();


        return $prep->rowCount();

    }


    public static function duplicateOffer($offer_id)
    {
        $dupe = self::selectOneQuery($offer_id)->fetch(PDO::FETCH_OBJ);
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO offer (offer_name, description, url, payout, status, offer_timestamp, created_by, campaign_id) VALUES(:offer_name, :description, :url, :payout, 0, :timestamp, :created_by, :campaign_id)";
        $prep = $db->prepare($sql);

        $date = date("Y-m-d H:m:s");

        $offerName = $dupe->offer_name." - Copy";
        $user_id = Session::userID();

        $prep->bindParam(":offer_name", $offerName);
        $prep->bindParam(":description", $dupe->description);
        $prep->bindParam(":url", $dupe->url);
        $prep->bindParam(":payout", $dupe->payout);
        $prep->bindParam(":timestamp", $date);
        $prep->bindParam(":created_by", $user_id);
        $prep->bindParam(":campaign_id", $dupe->campaign_id);

        $prep->execute();

        $lastID = $db->lastInsertId();


        // insert god's ID
        $sql = "INSERT INTO rep_has_offer (rep_idrep, offer_idoffer, payout) VALUES(:user_id, :offer_id, 0)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":offer_id", $lastID);

        $prep->execute();


        $sql = "SELECT rep_idrep, payout FROM rep_has_offer WHERE offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":offer_id", $offer_id);
        $prep->execute();

        $user_id_list = multiDimentialToSingular($prep->fetchAll(PDO::FETCH_NUM));


        if (!empty($user_id_list)) {


            $insertValues = array();
	        $questionMarks = [];
            for ($i = 0; $i < count($user_id_list); ($i = $i + 2)) {
                $questionMarks[] = "(?,?,?)";
                $insertValues[] = $user_id_list[$i];
                $insertValues[] = $lastID;
                $insertValues[] = $user_id_list[$i + 1];

            }


            $sql2 = 'INSERT IGNORE INTO rep_has_offer (rep_idrep, offer_idoffer, payout) VALUES '.implode(',',
                    $questionMarks);
            $stmt2 = $db->prepare($sql2);


            $success = $stmt2->execute($insertValues);

            $dupeRules = new Rules($offer_id);
            $dupeRules->duplicateRules($lastID);

            Caps::duplicateCapRules($offer_id, $lastID);


            return $success;

        }


    }

    public static function updateOfferId($offerId, $newOfferId)
    {
        $db = DatabaseConnection::getInstance();
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        $sql = "UPDATE offer SET idoffer = :newID WHERE idoffer = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":newID", $newOfferId);
        $prep->bindParam(":id", $offerId);

        $result = $prep->execute();
        $db->query("SET FOREIGN_KEY_CHECKS = 1");

        return $result;
    }


    // SELECT ONE
    public function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM offer WHERE idoffer = :id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }


    public static function selectOwnedOffers($userType)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        switch ($userType) {
            case \App\Privilege::ROLE_GOD:
            case \App\Privilege::ROLE_ADMIN:
                $sql = "SELECT * FROM offer";
                break;

            case \App\Privilege::ROLE_MANAGER:
                $sql = "SELECT * FROM offer        INNER JOIN rep
                    ON
                    rep.lft > :left AND rep.rgt < :right
                    INNER JOIN
                        rep_has_offer
                    ON
                       rep_has_offer.rep_idrep = rep.idrep AND  rep_has_offer.offer_idoffer = offer.idoffer OR offer.created_by = :user_id
                       ";
                break;

            case \App\Privilege::ROLE_AFFILIATE:
            default:
                $sql = "SELECT * FROM offer INNER JOIN rep_has_offer ON rep_has_offer.offer_idoffer = offer.idoffer AND rep_has_offer.rep_idrep = :user_id";
                break;

        }


        $prep = $db->prepare($sql);

        if ($userType == \App\Privilege::ROLE_MANAGER) {
            $prep->bindParam(":left", Session::userData()->lft);
            $prep->bindParam(":right", Session::userData()->rgt);
        }

        $userId = Session::userID();
        $prep->bindParam(":user_id", $userId);
        $prep->execute();

        return $prep;
    }

    public static function selectOneQuery($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM offer WHERE idoffer = :id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }


    // DELETE
    public function Delete($id)
    {
        $dbc = new \dboptions();
        $dbc->dbDelete('offer', 'idoffer', $id);
    }


    //Input: Offer ID
    //Output: Changes offer status, then Redirects
    static function ChangeOfferStatus($id, $redirect_to)
    {


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $prep = $db->prepare("SELECT * FROM offer WHERE idoffer = :id");
        $prep->bindParam(":id", $id);
        $prep->execute();
        $offerObj = $prep->fetch(PDO::FETCH_OBJ);

        $currentStatus = $offerObj->status;
        if ($currentStatus == 1) {
            $STATUS = 0;
        } else {
            $STATUS = 1;
        }


        $sql = " UPDATE offer SET status = :status WHERE idoffer = :id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $STATUS, PDO::PARAM_INT);
        $stmt->execute();
        send_to($redirect_to);


    }


} // end class


?>