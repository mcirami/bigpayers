<?php
/*
* =======================================================================
* CLASSNAME:        rep
* DATE CREATED:      16-05-2017
* FOR TABLE:          rep
* FOR DATA BASE:    trackyourstats
* IMPORTANT:
* the 'sanitize()' keyword is a defined function to prevent sql injection located @ lib/Functions.php
* 'post()' is also defined function located @ lib/funtions.php
* You can further improve these functions if necessary.
* =======================================================================
*/
namespace LeadMax\TrackYourStats\User;
//
//include '../modifiedPreorderTreeTraversalfiedPreorderTreeTraversal.php';
//include '../Permissions.php';

use App\Privilege;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Mail;
use LeadMax\TrackYourStats\System\Session;
use PDO;

//Begin class
class User extends Login
{


    const STATUS_ACTIVE = 1;
    const STATUS_IN_ACTIVE = 0;
    const STATUS_BANNED = -1;

    public $user_id = -1;

    public $userData;


    public function __construct($userID = false)
    {
        if ($userID) {
            $this->user_id = $userID;
            $this->userData = $this->queryUserRow()->fetch(PDO::FETCH_ASSOC);
        }
    }

    public static function findRepType($REP_ID)
    {
        $priv = new Privileges();
        $usr = $priv->SelectOneRepId($REP_ID);

        $repType = \App\Privilege::ROLE_UNKNOWN;

        //User was not found in privileges table
        if (!$usr) {
            return $repType;
        }

        if ($usr->is_god == 1) {
            $repType = \App\Privilege::ROLE_GOD;
        }
        if ($usr->is_admin == 1) {
            $repType = \App\Privilege::ROLE_ADMIN;
        }
        if ($usr->is_manager == 1) {
            $repType = \App\Privilege::ROLE_MANAGER;
        }
        if ($usr->is_rep == 1) {
            $repType = Privilege::ROLE_AFFILIATE;
        }

        return $repType;
    }

    public static function updateUserStatus($userId, $status)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep SET status = :status WHERE idrep = :userId";
        $prep = $db->prepare($sql);
        $prep->bindParam(":userId", $userId);
        $prep->bindParam(":status", $status);

        return $prep->execute();
    }


    public static function sendWelcomeEmail($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT email, first_name FROM rep WHERE idrep = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $user_id);
        $prep->execute();

        $result = $prep->fetch(PDO::FETCH_ASSOC);

        if (!$result || empty($result)) {
            return false;
        }

        $email = $result["email"];
        $firstName = $result["first_name"];

        $company = Company::loadFromSession();
        $companyShortHand = $company->getShortHand();
        $loginUrl = $company->getLoginURL();
        $title = "Welcome to {$companyShortHand}!";

        $message = "Greetings {$firstName},
					<br/>
					You're account has been activated, to start, click <a href='{$loginUrl}'>here</a>!
					<br/>
					<br/>
					This is an automated message, please do not reply.
				
		";

        $mail = new Mail($email, $title, $message);

        return $mail->send();
    }


    public static function getUsersGlobalPostBackURL($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM user_postbacks WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->execute();
        $result = $prep->fetch(PDO::FETCH_ASSOC);

        if ($result == false) {
            return "";
        }

        return $result["url"];
    }

    public static function selectAllManagers()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_manager = 1";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }

    public static function selectAllAdmins()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_admin = 1";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }

    public static function updateUserId($id, $newId)
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $db->query("SET FOREIGN_KEY_CHECKS=0;");


        $queries = [
            "UPDATE rep SET idrep = ? WHERE idrep = ?",
            "UPDATE privileges SET rep_idrep = ? WHERE privileges.rep_idrep = ?",
            "UPDATE permissions SET aff_id = ? WHERE aff_id = ?",
            "UPDATE report_permissions SET user_id = ? WHERE user_id = ?",
            "UPDATE rep_has_offer SET rep_has_offer.rep_idrep = ? WHERE rep_idrep = ?",
        ];


        foreach ($queries as $query) {
            $prep = $db->prepare($query);
            if ($prep->execute([
                    $newId,
                    $id,
                ]) == false) {
                $db->query("SET FOREIGN_KEY_CHECKS=1;");


                return false;
            }
        }

        $db->query("SET FOREIGN_KEY_CHECKS=1;");

        return true;
    }



    function __get($name)
    {
        if (key_exists($name, $this->userData)) {
            return $this->userData[$name];
        }

        return null;
    }

    public static function printUsersToSelectBox($assocUserArray)
    {
        foreach ($assocUserArray as $user) {
            echo "<option value=\"{$user["idrep"]}\">{$user["user_name"]}</option>";
        }
    }

    public function queryUserRow()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE idrep = :userID";
        $prep = $db->prepare($sql);
        $prep->bindParam(":userID", $this->user_id);
        $prep->execute();

        return $prep;

    }

    function rebuild_tree($referrer_repid, $left)
    {


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        // the right value of this node is the left value + 1
        $right = $left + 1;

        $sql = 'SELECT idrep FROM rep WHERE referrer_repid= :parent ';
        $prep = $db->prepare($sql);
        $prep->bindParam(":parent", $referrer_repid);
        $prep->execute();


        // get all children of this node
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            // recursive execution of this function for each
            // child of this node
            // $right is the current right value, which is
            // incremented by the rebuild_tree function
            $right = $this->rebuild_tree($row['idrep'], $right);
        }

        // we've got the left value, and now that we've processed
        // the children of this node we also know the right value
        $sql = "UPDATE rep SET lft=:left, rgt=
        :right WHERE idrep=:parent ";

        $prep = $db->prepare($sql);
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->bindParam(":parent", $referrer_repid);

        $prep->execute();

        // return the right value of this node + 1
        return $right + 1;


    }

    // SELECT ALL
    public function SelectAll()
    {
        $dbc = new dboptions();
        $record = $dbc->rawSelect("SELECT * FROM rep");

        return $record->fetchAll(PDO::FETCH_OBJ);
    }

    // SELECT ALL ACTIVE repS
    public function SelectAllActivereps()
    {
        $dbc = new dboptions();
        $record = $dbc->rawSelect("SELECT * FROM rep");

        return $record->fetchAll(PDO::FETCH_OBJ);
    }


    public static function selectAllAffiliateIDs($customDBConnection = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        if ($customDBConnection) {
            $db = $customDBConnection;
        }
        $prep = $db->prepare("SELECT idrep FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND is_rep = 1");
        $prep->execute();

        return $prep;
    }


    // SELECT All 2
    public function select_all()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep ";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    // SELECT All ASSIGNABLE BRUHS
    public function select_all_assignables()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 0 WHERE idrep != 0 AND status = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }


    public static function selectOwnedManagers()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_manager = 1 WHERE lft > :left AND rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;
    }

    public static function selectAdmins()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_admin = 1 WHERE lft > :left AND rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;
    }

    public function selectAssignablesManager()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 0 WHERE idrep != 0 AND status = 1";
        //AND referrer_repid = :idrep


        $stmt = $db->prepare($sql);
        /* $stmt->bindParam(":idrep", $this->user_id); */
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }


    public function select_all_managers()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_manager = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }

    public function select_all_managers_num()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_manager = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt;
    }


    // SELECT All REPS
    public function select_all_reps()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchALL(PDO::FETCH_ASSOC);
    }


    // SELECT All 2
    public function select_all_num()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt;
    }


    public static function selectUsersByPrivileges($userType)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_";
        switch ($userType) {
            case \App\Privilege::ROLE_ADMIN:
                $sql .= "admin";
                break;

            case Privilege::ROLE_MANAGER:
                $sql .= "manager";
                break;

            case Privilege::ROLE_AFFILIATE:
                $sql .= "rep";
                break;
        }

        $sql .= " = 1 WHERE rep.lft > :left AND rep.rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;

    }


    // SELECT All 2
    public function selectAllManagerAffiliates($affid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1 WHERE rep.referrer_repid = :affid";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":affid", $affid);
        $stmt->execute();

        return $stmt;
    }

    static function selectAllOwnedAffiliates($selectAllColumns = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        if ($selectAllColumns) {
            $sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1 WHERE rep.lft > :left AND rep.rgt < :right";
        } else {
            $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1 WHERE rep.lft > :left AND rep.rgt < :right";
        }

        $sql .= " ORDER BY user_name ASC";

        $prep = $db->prepare($sql);

        $userData = Session::userData();

        $prep->bindParam(":left", $userData->lft);
        $prep->bindParam(":right", $userData->rgt);

        $prep->execute();

        return $prep;
    }


    // SELECT ONE
    public static function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE idrep=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }


    // Register and Set priviliges for rep
    public function RegisterAndSetPriviliges($redirect_to)
    {


        $submit = post('button');

        if ($submit) {


            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


            if (post("user_name") == "") {
                return "EMPTY_USR_NAME";
            }
            if (post("password") == "" || post("confirmpassword") == "") {
                return "EMPTY_PWD";
            }


            $prep = $db->prepare("SELECT * FROM rep WHERE user_name = :repname OR email = :email");
            $repName = post("user_name");
            $EMAIL = post("EMAIL");

            $prep->bindParam(":repname", $repName);
            $prep->bindParam(":email", $EMAIL);

            $prep->execute();

            $oof = $prep->fetchAll(PDO::FETCH_ASSOC);


            if (count($oof) > 0) {
                return "USR_OR_EMAIL";
            }


            $db->beginTransaction();

            try {


                $confirmpassword = post('confirmpassword');
                $password = post('password');


                if ($password === $confirmpassword) {


                    $first_name = filter_var(post('first_name'), FILTER_SANITIZE_STRING);
                    $last_name = filter_var(post('last_name'), FILTER_SANITIZE_STRING);
                    $cell_phone = filter_var(post('cell_phone'), FILTER_SANITIZE_STRING);
                    $email = filter_var(post('email'), FILTER_SANITIZE_EMAIL);
                    $user_name = filter_var(post('user_name'), FILTER_SANITIZE_STRING);
                    $password = post('password');
                    $status = filter_var(post('status'), FILTER_SANITIZE_STRING);
                    $referrer_repid = filter_var(post('referrer_repid'), FILTER_SANITIZE_STRING);
                    $skype = filter_var(post('skype'), FILTER_SANITIZE_STRING);
                    $company_name = filter_var(post('company_name'), FILTER_SANITIZE_STRING);
                    $rep_timestamp = date('Y-m-d H:i:s');
                    $new_password = password_hash($password, PASSWORD_DEFAULT);
                    $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
                    $sql = "INSERT INTO rep(first_name,last_name,cell_phone,email,user_name,password,status,referrer_repid,rep_timestamp, skype, company_name) VALUES(:first_name,:last_name,:cell_phone,:email,:user_name,:password,:status,:referrer_repid,:rep_timestamp, :skype,
:company_name)";
                    $stmt = $db->prepare($sql);

                    $stmt->bindparam(":first_name", $first_name);
                    $stmt->bindparam(":last_name", $last_name);
                    $stmt->bindparam(":cell_phone", $cell_phone);
                    $stmt->bindparam(":email", $email);
                    $stmt->bindparam(":user_name", $user_name);
                    $stmt->bindparam(":password", $new_password);
                    $stmt->bindparam(":status", $status);
                    $stmt->bindparam(":referrer_repid", $referrer_repid);
                    $stmt->bindparam(":rep_timestamp", $rep_timestamp);
                    $stmt->bindparam(":skype", $skype);
                    $stmt->bindparam(":company_name", $company_name);
                    $stmt->execute();

                    $repID = $db->lastInsertId();

                    $repType = post("priv");
                    $sql2 = 'INSERT INTO privileges (rep_idrep, ';

	                switch ($repType) {

                        case \App\Privilege::ROLE_ADMIN:
                            $sql2 .= 'is_admin ';
                            break;
                        case Privilege::ROLE_MANAGER:
                            $sql2 .= "is_manager";
                            break;
                        case Privilege::ROLE_AFFILIATE:
                            $sql2 .= "is_rep";
                            break;

                        default:
                            $sql2 .= "is_rep";
                            break;
                    }

                    $sql2 .= ") VALUES(".$repID.", 1) ";

                    //                echo $sql2;
                    $stmt2 = $db->prepare($sql2);

                    //                    var_dump($sql2);

                    $stmt2->execute();
                    //                $stmt2->debugDumpParams();


                    //set permissions
                    $permissions = new Permissions();

                    $list = [];

                    $list = Permissions::defaultUserPermissions($list, $repType);

                    if (!empty($_POST["permissions"])) {

                        foreach ($_POST["permissions"] as $key => $val) {
                            $list[$val] = 1;
                        }

                    }


                    $list["aff_id"] = $repID;


                    if (!$permissions->createPermissions($list)) {
                        dd("PERMISSION_CREATE_ERROR");
                    }


                    if (isset($_POST["referralCheckBox"])) {
                        $options = [
                            "start_date" => $_POST["start_date"],
                            "end_date" => $_POST["end_date"],
                            "referral_type" => $_POST["referral_type"],
                            "payout" => $_POST["amount"],
                        ];
                        if (!Referrals::addReferral($_POST["referralSelectBox"], $repID, $options)) {
                            $db->rollBack();
                            die("<h1> ERROR </h1>");
                        }
                    }


                    $this->rebuild_tree(1, 1);

                    if ($repType == Privilege::ROLE_AFFILIATE) {
                        RepHasOffer::assignAffiliateToPublicOffers($repID);

                        ReportPermissions::createPermissions($repID);
                    }


                    Bonus::assignUsersInheritableBonuses([$repID], $referrer_repid);


                    $db->commit();

                } else {
                    return "PWD";
                }


            } catch (\Exception $e) {
                //An exception has occured, which means that one of our database queries
                //failed.
                //Print out the error message.
                //                echo "ERROR = " . $e->getMessage();
                //Rollback the transaction.
                $db->rollBack();

                die("<h1> ERROR: USER NOT SAVED </h1>".$e->getMessage()); // If there is an error, DIE, escape function
            }

            send_to($redirect_to . $repID);  //If there is no errors redirect


        }

    }




    //INPUT: An rep's ID
    //OUTPUT: True if the current logged in rep id is the inputed aff's referrer id
    public function hasRep($affid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT lft, rgt FROM rep WHERE idrep = :affid";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $affid);
        $prep->execute();
        $rep = $prep->fetch(PDO::FETCH_ASSOC);

        $currentrep = User::SelectOne(Session::userID());


        if ($rep["lft"] > $currentrep->lft && $rep["rgt"] < $currentrep->rgt) {
            return true;
        }

        return false;

    }

    static function hasAffiliate($affid)
    {
        $user = new User();

        return $user->hasRep($affid);
    }


    public static function userOwnsUser($user_id, $affiliate_id)
    {
        $originalUser = self::SelectOne($user_id);

        $userToBeChecked = self::SelectOne($affiliate_id);


        return ($originalUser->lft < $userToBeChecked->lft && $originalUser->rgt > $userToBeChecked->rgt);
    }

    public function checkAffView()
    {
        if (isset($_COOKIE["aff_view"])) {
            if ($this->hasRep($_COOKIE["aff_view"])) {
                return true;
            }

            return false;

        }

        return false;
    }


    public function is_loggedin()
    {
        return true;
        //    if (isset($_SESSION['rep_session'])) {
        //        return true;
        //    } else {
        //
        //        return false;
        //    }
    }

    // SELECT getCount
    static function getCount()
    {
        $dbc = new \dboptions();
        $record = $dbc->rawSelect(" SELECT COUNT(*) FROM rep");

        return $record->fetchColumn();

    }

    // SELECT getCount
    static function getCountRepType($REP_TYPE)
    {

        $dbc = new \dboptions();
        $sql = "SELECT COUNT(*) FROM rep ";

        switch ($REP_TYPE) {
            case 0:
                break;
            case 1:
                $sql .= "INNER JOIN privileges ON privileges.is_admin = 1 AND privileges.rep_idrep = rep.idrep ";
                break;
            case 2:
                $sql .= "INNER JOIN privileges ON privileges.is_manager = 1 AND privileges.rep_idrep = rep.idrep ";
                break;
            case 3:
                $sql .= "INNER JOIN privileges ON privileges.is_rep = 1 AND privileges.rep_idrep = rep.idrep ";
                break;
            default:

                break;

        }

        $record = $dbc->rawSelect($sql);

        return $record->fetchColumn();

    }


    // SELECT by page


    public function Update($redirect_to, $isGod = false)
    {
        $id = post('idrep');
        $first_name = filter_var(post('first_name'), FILTER_SANITIZE_STRING);
        $last_name = filter_var(post('last_name'), FILTER_SANITIZE_STRING);
        $cell_phone = filter_var(post('cell_phone'), FILTER_SANITIZE_STRING);
        $user_name = filter_var(post('user_name'), FILTER_SANITIZE_STRING);
        $password = post('password');
        $status = filter_var(post('status'), FILTER_SANITIZE_STRING);
        $referrer_repid = filter_var(post('referrer_repid'), FILTER_SANITIZE_STRING);
        $email = filter_var(post('email'), FILTER_SANITIZE_EMAIL);

        $skype = filter_var(post('skype'), FILTER_SANITIZE_EMAIL);
        $company_name = filter_var(post('company_name'), FILTER_SANITIZE_EMAIL);


        $priviliges = new Privileges();
        if ($priviliges->findRepType($id) == \App\Privilege::ROLE_GOD) {
            $userIsGod = true;
            $referrer_repid = 0;
        }


        $submit = post('button');
        if ($submit) {


            if ($password != post("confirmpassword")) {
                return "PWD_NO_MATCH";
            }


            $passwordChange = false;
            if (post("password") != "" && post("confirmpassword") != "" && post("password") == post("confirmpassword")) {
                $sql = " UPDATE rep SET  first_name =:first_name,last_name =:last_name,cell_phone =:cell_phone,user_name =:user_name,password =:password,status =:status,email = :email, skype=:skype, company_name=:company_name ";
                $passwordChange = true;
            } else {
                $sql = " UPDATE rep SET  first_name =:first_name,last_name =:last_name,cell_phone =:cell_phone,user_name =:user_name,status =:status, email = :email, skype = :skype, company_name = :company_name ";
            }

            if (Session::userType() == \App\Privilege::ROLE_GOD || Session::userType() == \App\Privilege::ROLE_ADMIN) {
                $sql .= ",referrer_repid =:referrer_repid";
            }

            $sql .= " WHERE idrep = :id ";


            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $stmt = $db->prepare($sql);


            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':cell_phone', $cell_phone);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':skype', $skype);
            $stmt->bindParam(':company_name', $company_name);

            $stmt->bindParam(':status', $status);
            if (Session::userType() == \App\Privilege::ROLE_GOD || Session::userType() == \App\Privilege::ROLE_ADMIN) {
                $stmt->bindParam(':referrer_repid', $referrer_repid);
            }


            if ($passwordChange) {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $password);

            }

            $stmt->execute();


            if (post("priv") && $isGod && !isset($userIsGod)) {

                $priv = post("priv");


                $admin = 0;
                $manager = 0;
                $rep = 0;

                switch ($priv) {
                    case \App\Privilege::ROLE_ADMIN:
                        $admin = 1;
                        $repType = \App\Privilege::ROLE_ADMIN;
                        break;

                    case Privilege::ROLE_MANAGER:
                        $manager = 1;
                        $repType = Privilege::ROLE_MANAGER;
                        break;

                    case Privilege::ROLE_AFFILIATE:
                        $rep = 1;
                        $repType = Privilege::ROLE_AFFILIATE;
                        break;

                    default:
                        //default to rep
                        $rep = 1;
                        $repType = Privilege::ROLE_AFFILIATE;
                        break;
                }


                //first clear privileges
                $sql = "UPDATE privileges SET is_admin = :admin , is_manager = :manager, is_rep = :rep WHERE rep_idrep = :idrep";


                $prep = $db->prepare($sql);

                $prep->bindParam(":admin", $admin);
                $prep->bindParam(":manager", $manager);
                $prep->bindParam(":rep", $rep);
                $prep->bindParam(":idrep", $id);
                if ($id != 1) {
                    $prep->execute();
                }


            }

            if (!isset($userIsGod) && isset($repType)) {


                //set permissions

                $permissions = new Permissions();

                $list = [];
                $list = Permissions::defaultUserPermissions($list, $repType);

                if (!empty($_POST["permissions"])) {

                    foreach ($_POST["permissions"] as $key => $val) {
                        $list[$val] = 1;
                    }

                }


                //check if they didn't have permissions before
                if (!Permissions::permissionsExist($id)) {
                    $list["aff_id"] = $id;
                    $permissions->createPermissions($list);

                } else {
                    $permissions->updatePermissions($list, $id);
                }

            }
            if (isset($_POST["referralCheckBox"])) {
                $options = [
                    "start_date" => $_POST["start_date"],
                    "end_date" => $_POST["end_date"],
                    "referral_type" => $_POST["referral_type"],
                    "payout" => $_POST["amount"],
                ];
                if (!Referrals::addReferral($_POST["referralSelectBox"], $id, $options)) {
                    $db->rollBack();
                    die("<h1> ERROR </h1>");
                }
            }


            Tree::rebuild_tree(1, 1);

            if (isset($_POST["referrer_box"])) {
                Referrals::updateReferrer($id, $_POST["referrer_box"]);
            }

            Bonus::assignUsersInheritableBonuses([$id], $referrer_repid);

            send_to($redirect_to);

        }

    }

} // end class
    
