<?php
/*
* =======================================================================
* CLASSNAME:        privileges
* DATE CREATED:      16-05-2017
* FOR TABLE:          privileges
* FOR DATA BASE:    trackyourstats
* IMPORTANT:
* the 'sanitize()' keyword is a defined function to prevent sql injection located @ lib/Functions.php
* 'post()' is also defined function located @ lib/funtions.php
* You can further improve these functions if necessary.
* =======================================================================
*/

namespace LeadMax\TrackYourStats\User;

use App\Privilege;
use PDO;


//Begin class
class Privileges
{
    public $idprivileges;
    public $rep_idrep;
    public $is_god;
    public $is_admin;
    // Table Columns
    //(idprivileges,rep_idrep,is_god,is_admin)
    // Table Prepare Columns
    //(:idprivileges,:rep_idrep,:is_god,:is_admin)

    //Constructor
    public function __construct(
        $idprivileges = '',
        $rep_idrep = '',
        $is_god = '',
        $is_admin = ''
    ) {
        $this->idprivileges = $idprivileges;
        $this->rep_idrep = $rep_idrep;
        $this->is_god = $is_god;
        $this->is_admin = $is_admin;
    }


    public static function create($user_id, $user_type)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO privileges(rep_idrep, is_god, is_admin, is_manager, is_rep) VALUES(:user_id,";

        $sql .= ($user_type == \App\Privilege::ROLE_GOD) ? "1," : "0,";
        $sql .= ($user_type == \App\Privilege::ROLE_ADMIN) ? "1," : "0,";
        $sql .= ($user_type == Privilege::ROLE_MANAGER) ? "1," : "0,";
        $sql .= ($user_type == \App\Privilege::ROLE_AFFILIATE) ? "1" : "0";

        $sql .= ")";

        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }

    //Input: User ID of the user
    //Output: Returns the User type of the referrer
    public function findReferrerType($REP_ID)
    {
        //gets user by User id
        $usr = $this->SelectOneRepId($REP_ID);

        return $this->findRepType($usr->referrer_repid);
    }

    public static function findUserType($user_id)
    {
        $user = self::selectOneByUserStatic($user_id);

        $type = \App\Privilege::ROLE_UNKNOWN;

        if ($user == false) {
            return $type;
        }

        if ($user->is_god == 1) {
            return \App\Privilege::ROLE_GOD;
        }

        if ($user->is_admin == 1) {
            return Privilege::ROLE_ADMIN;
        }

        if ($user->is_manager == 1) {
            return Privilege::ROLE_MANAGER;
        }

        if ($user->is_rep == 1) {
            return Privilege::ROLE_AFFILIATE;
        }

        return $type;
    }

    //Input: User ID
    //Output: Returns User type as enum (integer)
    public function findRepType($REP_ID)
    {
        $usr = $this->SelectOneRepId($REP_ID);

        $repType = \App\Privilege::ROLE_UNKNOWN;

        //User was not found in privileges table
        if (!$usr) {
            return $repType;
        }

        if ($usr->is_god == 1) {
            $repType = \App\Privilege::ROLE_GOD;
        }
        if ($usr->is_admin == 1) {
            $repType = Privilege::ROLE_ADMIN;
        }
        if ($usr->is_manager == 1) {
            $repType = Privilege::ROLE_MANAGER;
        }
        if ($usr->is_rep == 1) {
            $repType = Privilege::ROLE_AFFILIATE;
        }

        return $repType;
    }


    // SELECT ALL
    public function SelectAll()
    {
        $dbc = new dboptions();
        $record = $dbc->rawSelect("SELECT * FROM privileges");

        return $record->fetchAll(PDO::FETCH_OBJ);
    }

    // GER USER PRIVILIGES
    public function GetPrivileges($irdep)
    {
        $dbc = new dboptions();
        $record = $dbc->rawSelect("SELECT * FROM privileges WHERE rep_idrep = :irdep");
        $stmt->bindParam(':irdep', $irdep, PDO::PARAM_INT);

        return $record->fetch(PDO::FETCH_OBJ);
    }

    // SELECT All 2
    public function select_all()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM privileges ";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_OBJ);
    }

    // SELECT All 2
    public function get_count()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT COUNT(*) FROM privileges ";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // Get column names
    public function get_column_names()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $rows = $db->query("SELECT * FROM privileges LIMIT 1");
        for ($i = 0; $i < $rows->columnCount(); $i++) {
            $column = $rows->getColumnMeta($i);
            $columns[] = $column ['name'];
        }

        return $columns;
    }

    public static function selectOneByUserStatic($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM privileges WHERE rep_idrep=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // SELECT ONE
    public function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM privileges WHERE idprivileges=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $check = $stmt->fetch(PDO::FETCH_OBJ);
        print_r($check);
    }

    // SELECT ONE BASED ON REPID
    public function SelectOneRepId($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM privileges WHERE rep_idrep=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);

    }

    // DELETE
    public function Delete($id)
    {
        $dbc = new dboptions();
        $dbc->dbDelete('privileges', 'idprivileges', $id);
    }

    // INSERT
    public function Insert($redirect_to)
    {
        $submit = post('button');
        if ($submit) {

            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $rep_idrep = post('rep_idrep');
            $is_god = post('is_god');
            $is_admin = post('is_admin');

            $sql = "INSERT INTO privileges(rep_idrep,is_god,is_admin) VALUES(:rep_idrep,:is_god,:is_admin)";

            $stmt = $db->prepare($sql);

            $stmt->bindparam(":rep_idrep", $rep_idrep);
            $stmt->bindparam(":is_god", $is_god);
            $stmt->bindparam(":is_admin", $is_admin);
            $stmt->execute();


            send_to($redirect_to);
        }
    }

    // UPDATE
    public function Update($redirect_to)
    {
        $id = post('idprivileges');
        $this->rep_idrep = post('rep_idrep');
        $this->is_god = post('is_god');
        $this->is_admin = post('is_admin');

        $submit = post('button');
        if ($submit) {

            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = " UPDATE privileges SET  rep_idrep ='$this->rep_idrep',is_god ='$this->is_god',is_admin ='$this->is_admin' WHERE idprivileges = :id ";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            send_to($redirect_to);
        }

    }


} // end class