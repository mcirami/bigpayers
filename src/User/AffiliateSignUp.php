<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/9/2018
 * Time: 11:42 AM
 */

namespace LeadMax\TrackYourStats\User;


use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Connection;

class AffiliateSignUp
{
    private $db = false;

    public $status = "IDLE";


    const IDLE = "IDLE";
    const SUCCESS = "SUCCESS";
    const DATABASE_ERROR = "DATABASE_ERROR";
    const USERNAME_OR_EMAIL_EXISTS = "USERNAME_OR_EMAIL_EXISTS";
    const INVALID_COMPANY_ID = "INVALID_COMPANY_ID";
    const INVALID_EMAIL = "INVALID_EMAIL";
    const INVALID_USERNAME = "INVALID_USERNAME";
    const PASSWORD_MISMATCH = "PASSWORD_MISMATCH";
    const MISSING_FIELDS = "MISSING_OR_INVALID_FIELDS";

    private $forceLive = true;


    public function __construct()
    {
        if ($this->checkRequiredFields()) {
            if ($this->userNameOrEmailExists($_POST["tys_username"], $_POST["tys_email"]) == false) {
                if ($this->registerUser()) {
                    $this->setResult(self::SUCCESS);
                } else {
                    $this->setResult(self::DATABASE_ERROR);
                }
            } else {
                $this->setResult(self::USERNAME_OR_EMAIL_EXISTS);
            }

        }

    }


    public static function queryFetchPendingAffiliates()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE rep.referrer_repid = 1 AND status = 0";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }

    public function getResult()
    {
        return $this->status;
    }

    public static function notifyUsersOfRegistration($affiliate_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT aff_id FROM permissions WHERE approve_affiliate_sign_ups = 1";
        $prep = $db->prepare($sql);
        $prep->execute();

        $users = $prep->fetchAll(\PDO::FETCH_OBJ);


        if (empty($users) || $users == false) {
            return false;
        }

        $user_ids = [];
        foreach ($users as $user) {
            $user_ids [] = $user->aff_id;
        }


        $msg = "A new affiliate sign up has been requested, to over look and approve it please user this link  <a href=\"activate_affiliate.php?id={$affiliate_id}\">here</a>";


        return \LeadMax\TrackYourStats\System\Notifications::sendNotification($user_ids, 1, "Affiliate Sign Up Request",
            $msg);
    }

    public function registerUser()
    {

        $user_name = $_POST["tys_username"];
        $password = password_hash($_POST["tys_password"], PASSWORD_DEFAULT);

        $firstName = $this->getP("tys_first_name");
        $lastName = $this->getP("tys_last_name");
        $skype = $this->getP("tys_skype");
        $company_name = $this->getP("tys_company_name");

		if ($this->getP("mid") != "") {
			$mid = intval($this->getP("mid"));
			$status = 1;
		} else {
			$mid = 1;
			$status = 0;
		}

        $email = $_POST["tys_email"];

        $timestamp = date("Y-m-d H:i:s");

        $sql = "
INSERT INTO rep (first_name, last_name, email, user_name, password, status, referrer_repid, rep_timestamp, lft,rgt, skype, company_name) VALUES(:first_name, :last_name, :email, :user_name, :password, :status, :mid, :date, 0,0, :skype, :company_name);
";

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $prep = $db->prepare($sql);


        $prep->bindParam(":first_name", $firstName);
        $prep->bindParam(":last_name", $lastName);
        $prep->bindParam(":email", $email);
        $prep->bindParam(":user_name", $user_name);
        $prep->bindParam(":password", $password);
        $prep->bindParam(":status", $status);
        $prep->bindParam(":mid", $mid);
        $prep->bindParam(":date", $timestamp);
        $prep->bindParam(":skype", $skype);
        $prep->bindParam(":company_name", $company_name);
	    

        if ($prep->execute()) {

			$id = $db->lastInsertId();
	        self::notifyUsersOfRegistration($db->lastInsertId());

	        if ($mid != 1) {
		        \LeadMax\TrackYourStats\User\Create::activateAffiliate( $id, $mid );
	        }

            return true;
        } else {
            return false;
        }
    }

    private function userNameOrEmailExists($userName, $email)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE user_name = :user_name OR email = :email";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_name", $userName);
        $prep->bindParam(":email", $email);
        $prep->execute();
        if ($prep->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }


    private function getP($varName)
    {
        return (isset($_POST[$varName])) ? $_POST[$varName] : "";
    }


    private function setResult($status)
    {
        $this->status = $status;
    }

    public function checkRequiredFields()
    {

        if ($this->verifyOtherFields() == false) {
            $this->setResult(self::MISSING_FIELDS);

            return false;
        }


        if ($this->verifyUserName() == false) {
            $this->setResult(self::INVALID_USERNAME);

            return false;
        }

        if ($this->verifyPassword() == false) {
            $this->setResult(self::PASSWORD_MISMATCH);

            return false;
        }

        if ($this->verifyEmail() == false) {
            $this->setResult(self::INVALID_EMAIL);

            return false;
        }


        return true;
    }

    private function verifyEmail()
    {
        if (isset($_POST["tys_email"]) == false) {
            return false;
        } else {
            if (filter_var($_POST["tys_email"], FILTER_VALIDATE_EMAIL) == false) {
                return false;
            }
        }

        return true;
    }

    private function verifyPassword()
    {
        if (isset($_POST["tys_password"]) == false || isset($_POST["tys_confirm_password"]) == false) {
            return false;
        } else {
            if ($_POST["tys_password"] != $_POST["tys_confirm_password"]) {
                return false;
            }
            if (strlen($_POST["tys_password"]) <= 7) {
                return false;
            }
        }

        return true;
    }


    private function verifyUserName()
    {
        if (isset($_POST["tys_username"]) == false) {
            return false;
        } else {
            if (strlen($_POST["tys_username"]) <= 3) {
                return false;
            }
        }


        return true;
    }

    private function verifyCompanyID()
    {

        if (isset($_POST["tys_cid"]) == false) {
            return false;
        } else {
            $this->db = Connection::createConnectionWithCompanyID($_POST["tys_cid"], $this->forceLive);
            if ($this->db == false) {
                return false;
            }

            return true;
        }
    }

    private function verifyOtherFields()
    {
        if (!isset($_POST["tys_first_name"])
            || !isset($_POST["tys_last_name"])
        ) {
            return false;
        } else {
            if (strlen($_POST["tys_first_name"]) <= 2 || strlen($_POST["tys_last_name"]) <= 2 ) {
                return false;
            }

            return true;
        }
    }

}