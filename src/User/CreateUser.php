<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/26/2018
 * Time: 2:03 PM
 */

namespace LeadMax\TrackYourStats\User;


use LeadMax\TrackYourStats\Offer\RepHasOffer;

class CreateUser
{

    public $idrep;
    public $first_name;
    public $last_name;
    public $cell_phone;
    public $email;
    public $user_name;
    public $password;
    public $status;
    public $referrer_repid;
    public $rep_timestamp;
    public $lft;
    public $rgt;
    public $skype;
    public $company_name;


    public $permissions;

    public $userType;


    private function insertIntoTableRep()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO rep(first_name, last_name, cell_phone, email, user_name, password, status, referrer_repid, rep_timestamp, lft, rgt, skype, company_name)
				VALUES(:first_name, :last_name, :cell_phone, :email, :user_name, :password,:status, :referrer_repid, :rep_timestamp, :lft, :rgt, :skype, :company_name)";

        $prep = $db->prepare($sql);
        $prep->bindParam(":first_name", $this->first_name);
        $prep->bindParam(":last_name", $this->last_name);
        $prep->bindParam(":cell_phone", $this->cell_phone);
        $prep->bindParam(":email", $this->email);
        $prep->bindParam(":user_name", $this->user_name);
        $prep->bindParam(":password", $this->password);
        $prep->bindParam(":status", $this->status);
        $prep->bindParam(":referrer_repid", $this->referrer_repid);
        $prep->bindParam(":rep_timestamp", $this->rep_timestamp);

        $prep->bindParam(":lft", $this->lft);
        $prep->bindParam(":rgt", $this->rgt);

        $prep->bindParam(":skype", $this->skype);
        $prep->bindParam(":company_name", $this->company_name);


        if ($prep->execute()) {
            $this->idrep = $db->lastInsertId();

            return true;
        } else {
            return false;
        }

    }

    public function save()
    {
        if ($this->verifyUserData()) {
            $this->checkAndSetOptionalFields();
            $this->hashPassword();
            if ($this->insertIntoTableRep()) {
                $this->registerUserPrivileges();
                $this->registerUserPermissions();

                if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
                    $this->assignPublicOffers();
                    $this->registerReportPermissions();
                }

                $this->assignInheritableBonuses();

                Tree::rebuild_tree(1, 1);

                return true;
            }

        }


        return false;
    }

    private function assignInheritableBonuses()
    {
        Bonus::assignUsersInheritableBonuses([$this->idrep], $this->referrer_repid);
    }

    private function registerReportPermissions()
    {
        return ReportPermissions::createPermissions($this->idrep);
    }

    private function assignPublicOffers()
    {
        return RepHasOffer::assignAffiliateToPublicOffers($this->idrep);
    }

    private function registerUserPermissions()
    {

        $permissions = new Permissions();

        $list = [];

        $list = Permissions::defaultUserPermissions($list, $this->userType);

        if (isset($this->permissions) && empty($this->permissions) == false) {
            foreach ($this->permissions as $key => $val) {
                $list[$val] = 1;
            }
        }


        return $permissions->createPermissionsClean($this->idrep, $list);
    }

    private function registerUserPrivileges()
    {
        return Privileges::create($this->idrep, $this->userType);
    }


    private function checkAndSetOptionalFields()
    {
        $this->generateTimestampIfNotSet();

        if (isset($this->skype) == false) {
            $this->skype = "";
        }

        if (isset($this->company_name) == false) {
            $this->company_name = "";
        }

        if (isset($this->cell_phone) == false) {
            $this->cell_phone = "";
        }

        if (isset($this->status) == false) {
            $this->status = 1;
        }

        if (isset($this->lft) == false || isset($this->rgt) == false) {
            $this->lft = 0;
            $this->rgt = 0;
        }

    }

    private function hashPassword()
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }


    private function generateTimestampIfNotSet()
    {
        if (isset($this->rep_timestamp) == false) {
            $this->rep_timestamp = date("Y-m-d H:i:s");
        }
    }

    private function verifyReferrerExistsAndActive()
    {
        $user = User::SelectOne($this->referrer_repid);
        if ($user && $user->status == 1) {
            return true;
        }

        return false;
    }


    private function verifyUserData()
    {
        return ($this->verifyEmail() && $this->verifyUserName() && $this->verifyReferrerExistsAndActive());
    }


    private function verifyUserName()
    {
        if (isset($this->user_name) == false) {
            return false;
        }


        return (self::doesUserNameExist($this->user_name) == false);
    }

    private function verifyEmail()
    {
        $this->email = filter_var($this->email, FILTER_VALIDATE_EMAIL);

        return (self::doesEmailExist($this->email) == false);
    }


    public static function doesUserNameExist($userName)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT user_name FROM rep WHERE user_name = :user_name";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_name", $userName);
        $prep->execute();

        return ($prep->rowCount() > 0);
    }

    public static function doesEmailExist($email)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT email FROM rep WHERE email = :email";
        $prep = $db->prepare($sql);
        $prep->bindParam(":email", $email);
        $prep->execute();


        return ($prep->rowCount() > 0);
    }

}
