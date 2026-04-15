<?php

/**
 * Created by PhpStorm.
 * User: dean
 * Date: 6/26/2017
 * Time: 1:24 PM
 */

namespace LeadMax\TrackYourStats\User;

use App\Privilege;
use LeadMax\TrackYourStats\System\Session;
use PDO;

define('ACCOUNT_TYPE', env('ACCOUNT_TYPE_TEXT'));
// permissions class to get permissions for entered user id

class Permissions
{

    public $permissions = array();
    public $repID = null;
    public $userType;

    public $affiliateOnlyPermissions = [self::SMS_CHAT];

    const CREATE_ADMINS = "create_admins";
    const CREATE_MANAGERS = "create_managers";
    const CREATE_AFFILIATES = "create_affiliates";
	const VIEW_ALL_USERS = "view_all_users";
    const CREATE_OFFERS = "create_offers";
    const EDIT_OFFER_RULES = "edit_offer_rules";
    const VIEW_POSTBACK = "view_postback";
    const EDIT_REFERRALS = "edit_referrals";
    const VIEW_FRAUD_DATA = "view_fraud_data";
    const EDIT_AFF_PAYOUT = "edit_aff_payout";
    const CREATE_NOTIFICATIONS = "create_notifications";
    const CREATE_BONUSES = "create_bonuses";
    const ASSIGN_BONUSES = "assign_bonuses";
    const EDIT_SALARIES = "edit_salaries";
    const PAY_SALARIES = "pay_salaries";
    const EDIT_OFFER_URLS = "edit_offer_urls";
    const APPROVE_OFFER_REQUESTS = "approve_offer_requests";
    const APPROVE_AFFILIATE_SIGN_UPS = "approve_affiliate_sign_ups";
    const EDIT_AFFILIATES = "edit_affiliates";
	const SMS_CHAT = "sms_chat";
	const VIEW_SMS_STATS = "view_sms_stats";
    const EDIT_REPORT_PERMISSIONS = "edit_report_permissions";
	const VIEW_ADV_REPORTS = "view_adv_reports";
	const VIEW_PAYOUTS = "view_payouts";
    const ADJUST_SALES = "adjust_sales";
    const BAN_USERS = "ban_users";
    const EMAIL_POOLS = 'email_pools';
    const ACCOUNT_TYPE = ACCOUNT_TYPE;

    public static $permissionsArray = [

        self::CREATE_ADMINS => ["description" => "Can Create Admin Accounts", "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN]],

        self::CREATE_MANAGERS => [
            "description" => "Can Create " . self::ACCOUNT_TYPE . " Accounts",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER],
        ],

        self::CREATE_AFFILIATES => [
            "description" => "Can Create Agent Accounts",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER],
        ],

        self::EDIT_AFFILIATES => [
            "description" => "Can Edit Agent Accounts",
            /*"required_permissions" => [self::CREATE_AFFILIATES],*/
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER, Privilege::ROLE_AFFILIATE],
        ],

        self::SMS_CHAT => [
	        'description' => 'Can use SMS Verification',
	        'allowed_user_types' => [Privilege::ROLE_GOD, Privilege::ROLE_AFFILIATE]
        ],

        self::VIEW_SMS_STATS => [
	        'description' => 'Can View SMS Stats',
	        'allowed_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER]
        ],

        self::VIEW_ALL_USERS => [
	        "description" => "Can View All Users",
	        "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN]
        ],

        self::CREATE_OFFERS => ["description" => "Can Create Offers", "allowed_user_types" => [\App\Privilege::ROLE_GOD]],

        self::EDIT_OFFER_RULES => [
            "description" => "Can Edit Offer Rules",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::VIEW_POSTBACK => [
            "description" => "Can View PostBack URL",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::EDIT_REFERRALS => [
            "description" => "Can Edit Affiliate Referrals",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::VIEW_FRAUD_DATA => [
            "description" => "Can View Fraud Data in Click Reports",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::EDIT_AFF_PAYOUT => [
            "description" => "Can Edit Affiliate Offer Payouts",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
        ],

        self::CREATE_NOTIFICATIONS => [
            "description" => "Can Create Notifications",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER],
        ],

        self::CREATE_BONUSES => [
            "description" => "Can Create And Edit Bonuses",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::ASSIGN_BONUSES => [
            "description" => "Can Assign Affiliates to Bonuses",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::EDIT_SALARIES => [
            "description" => "Can Edit Affiliate Salaries",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::PAY_SALARIES => [
            "description" => "Can Pay Affiliate Salaries",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::EDIT_OFFER_URLS => [
            "description" => "Can Edit or Create Offer URLs",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::APPROVE_OFFER_REQUESTS => [
            "description" => "Can Approve Offer Requests",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD],
        ],

        self::APPROVE_AFFILIATE_SIGN_UPS => [
            "description" => "Can Approve Affiliate Sign ups",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER],
        ],

        self::EDIT_REPORT_PERMISSIONS => [
            "description" => "Can Edit Affiliate Report Permissions",
            "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER],
        ],

        self::VIEW_PAYOUTS => [
	        "description" => "Can View Payouts",
	        "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
        ],

        self::VIEW_ADV_REPORTS => [
	        "description" => "Can View Adv Reports",
	        "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
        ],

        self::ADJUST_SALES => ["description" => "Can Adjust Sales", "allowed_user_types" => [\App\Privilege::ROLE_GOD]],


        self::BAN_USERS => ["description" => "Can Ban Users", "allowed_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER]],

//        self::EMAIL_POOLS => ['description' => 'Can use Email Pools', 'allowed_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER, Privilege::ROLE_AFFILIATE]],

//        self::SMS_CHAT => ['description' => 'Can use SMS Chat', 'allowed_user_types' => [Privilege::ROLE_AFFILIATE]],

        "aff_id" => "IGNORE",
    ];


    function __construct($repID = null)
    {
        $this->repID = $repID;

        $this->getPermissions();
        $this->getUserPermissions();
    }


    static function loadFromSession()
    {
        if ($_SESSION["permissions"] != null) {
            return unserialize($_SESSION["permissions"]);
        } else {
            return false;
        }
    }

    private function canPrintPermission($permission, $userType)
    {

        if(in_array($permission, $this->affiliateOnlyPermissions)) {
            return true;
        }


        if (isset($this->permissions[$permission]) == false || Session::permissions()->can($permission) == false) {
            return false;
        }

        $permissionDetails = self::$permissionsArray[$permission];


        if (isset($permissionDetails["allowed_user_types"])) {
            //check editing user
            if (in_array(Session::userType(), $permissionDetails["allowed_user_types"]) == false) {
                return false;
            }

            //check user being edited
            if (in_array($userType, $permissionDetails["allowed_user_types"]) == false) {
                return false;
            }

        }


        if (isset($permissionDetails["required_permissions"])) {
            foreach ($permissionDetails["required_permissions"] as $per) {
                if (Session::permissions()->can($per) == false) {
                    return false;
                }
            }
        }

        return true;

    }


    public function dumpPermissionsToJavascript($checkCheckBoxes = false)
    {
        echo "<script type=\"text/javascript\">";

        $this->dumpAdminLevelPermissions($checkCheckBoxes);

        $this->dumpManagerLevelPermissions($checkCheckBoxes);

        $this->dumpAffiliateLevelPermissions($checkCheckBoxes);

        echo "</script>";
    }


    private function dumpAdminLevelPermissions($checkCheckBoxes = false)
    {
        echo "function appendAdmin(){ ";
        echo " var p = $(\"#permissionsP\");
				p.empty();         	 ";

        foreach (self::$permissionsArray as $permission => $val) {
            if ($permission != "aff_id" && $permission != "sms_chat") {
                if ($this->canPrintPermission($permission, Privilege::ROLE_ADMIN)) {
                    $this->printPermission($permission, $checkCheckBoxes);
                }
            }
        }

        echo "}";


    }

    private function dumpManagerLevelPermissions($checkCheckBoxes = false)
    {

        echo "function appendManager(){ ";
        echo " var p = $(\"#permissionsP\");
				p.empty();         	 ";

        foreach (self::$permissionsArray as $permission => $val) {
            if ($permission != "aff_id" && $permission != "sms_chat") {
                if ($this->canPrintPermission($permission, Privilege::ROLE_MANAGER)) {
                    $this->printPermission($permission, $checkCheckBoxes);
                }
            }
        }

        echo "}";
    }


    private function dumpAffiliateLevelPermissions($checkCheckBoxes)
    {

        echo "function appendAffiliate(){ ";
        echo " var p = $(\"#permissionsP\");
				p.empty();         	 ";

        foreach (self::$permissionsArray as $permission => $val) {
            if ($permission != "aff_id") {
                if ($this->canPrintPermission($permission, Privilege::ROLE_AFFILIATE)) {
                    $this->printPermission($permission, $checkCheckBoxes);
                }
            }
        }

        echo "}";
    }


    private function printPermission($permission, $checked = false)
    {

        $isChecked = "";
        if ($checked) {
            $isChecked = $this->can($permission) ? ", 'checked':'checked'" : "";
        }

        echo " $('<input/>').attr({type:'checkbox',name:'permissions[]', class:'fixCheckBox', value:'{$permission}' {$isChecked} }).appendTo('#permissionsP');
         p.append(\"".self::$permissionsArray[$permission]["description"]." <br/>\");";
    }


    public function can($PERMISSION)
    {

        if (!isset( $this->permissions[$PERMISSION])) {
            return false;
        }

        try {

            if ($this->permissions[$PERMISSION] == 1) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log($e, $this);
        }

    }


    public function setZeros($list)
    {


        foreach (self::$permissionsArray as $key => $val) {
            if (!array_key_exists($key, $list) && $key !== "aff_id") {
                $list[$key] = 0;
            }
        }

        return $list;


    }

    static function permissionsExist($affid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM permissions WHERE aff_id = :affid LIMIT 1";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $affid);
        $prep->execute();
        if ($prep->rowCount() > 0) {
            return true;
        } else {
            return false;
        }


    }

    function validatePermissions($list)
    {

        $keys = array_keys($list);
        foreach ($keys as $key) {
            if (!isset(self::$permissionsArray[$key])) {
                return false;
            }
        }

        return true;
    }

    /*  "$list" should look like dis:

    $list = array([
                    "create_offers" => 1,
                    "create_affiliates" => 0
                  ]);                               */
    public function updatePermissions($list, $affid)
    {


        // if the permission wasn't assigned (wasn't checked in update page)
        // then set to zero
        $list = $this->setZeros($list);


        // makes sure the key names are a permission
        if (!$this->validatePermissions($list)) {
            return false;
        }


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE permissions SET ";


        $i = 0;
        foreach ($list as $key => $val) {
            $i++;

            if ($i != (count($list))) {
                $sql .= $key." =  ?, ";
            } else {
                $sql .= $key." = ? ";
            }
        }


        $values = array_values($list);

        $sql .= " WHERE aff_id = ? ";


        $values[] = $affid;


        $prep = $db->prepare($sql);


        if ($prep->execute($values)) {
            return true;
        }

        return false;

    }


    static function defaultUserPermissions($list, $userType)
    {
        // set default permissions
        switch ($userType) {
            case Privilege::ROLE_ADMIN:
                foreach (self::$permissionsArray as $permission => $description) {
                    if ($permission != "aff_id") {
                        $list[$permission] = 0;
                    }
                }
                break;
            case Privilege::ROLE_MANAGER:
                foreach (self::$permissionsArray as $permission => $description) {
                    if ($permission != "aff_id" && $permission !== "create_admins") {
                        $list[$permission] = 0;
                    }
                }

                break;

            case Privilege::ROLE_AFFILIATE:
                $list["create_offers"] = 0;
                $list["edit_offer_rules"] = 0;
                $list["create_admins"] = 0;
                $list["create_managers"] = 0;
                $list["create_affiliates"] = 0;
                $list["view_postback"] = 0;
                $list["edit_referrals"] = 0;
                $list["view_fraud_data"] = 0;

                break;

        }

        return $list;
    }


    public function createPermissionsClean($userId, array $permissionsList)
    {
        $permissionsList["aff_id"] = $userId;

        return $this->createPermissions($permissionsList);
    }

    public function createPermissions($list)
    {


        // makes sure the key names are a permission
        if (!$this->validatePermissions($list)) {
            return "VALIDATE_BAD";
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO permissions (";

        $keys = array_keys($list);
        for ($i = 0; $i < count($keys); $i++) {
            if ($i == count($keys) - 1) {
                $sql .= $keys[$i].") ";
            } else {
                $sql .= $keys[$i].", ";
            }
        }

        $sql .= "VALUES (";

        for ($i = 0; $i < count($keys); $i++) {
            if ($i == count($keys) - 1) {
                $sql .= "?)";
            } else {
                $sql .= "?, ";
            }

        }

        $values = array_values($list);


        $prep = $db->prepare($sql);
        if ($prep->execute($values)) {
            return true;
        }

        return "DB_ERROR";


    }

    function __get($name)
    {
        return $this->permissions[$name];
    }

    function getUserPermissions()
    {
        if ($this->repID == null) {
            return;
        }


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM permissions WHERE aff_id = :affid";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $this->repID);
        try {
            if ($prep->execute()) {
                $raw = $prep->fetch(PDO::FETCH_ASSOC);

                if (!$raw) {
                    return;
                }

                $this->permissions = $raw;
            } else {
                Log("Unknown getUserPermissions() Error", $this);
            }

        } catch (\Exception $e) {
            Log($e, $this);
        }


    }


    function getPermissions()
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DESCRIBE permissions";
        $prep = $db->prepare($sql);
        try {
            if ($prep->execute()) {
                $columnNames = $prep->fetchAll(PDO::FETCH_COLUMN);
                foreach ($columnNames as $key => $val) {
                    if ($val == "aff_id" || $val == "id") {
                        unset($columnNames[$key]);
                    }
                }

                $this->permissions = $columnNames;

            } else {
                Log("Unknown getPermissions() Error", $this);
            }

        } catch (\Exception $e) {
            Log($e, $this);
        }

    }


}