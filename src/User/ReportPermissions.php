<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/23/2018
 * Time: 12:02 PM
 */

namespace LeadMax\TrackYourStats\User;


use LeadMax\TrackYourStats\Database\DatabaseConnection;

class ReportPermissions
{

    public $userId;

    public $permissions = [];

    const OFFER_ID = "offer_id";
    const OFFER_NAME = "offer_name";
    const RAW_CLICKS = "raw_clicks";
    const UNIQUE_CLICKS = "unique_clicks";
    const CONVERSIONS = "conversions";
    const REVENUE = "revenue";
    const EPC = "epc";
    const FREE_SIGN_UPS = "free_sign_ups";

    const IGNORE = "IGNORE";


    private $permissionList = [];


    public function __construct($user_id = false)
    {
        if ($user_id) {
            $this->userId = $user_id;
            $this->loadUsersPermissions();
        }
    }

    public static function getAffiliates()
    {

        $affiliateList = User::selectAllOwnedAffiliates()->fetchAll(\PDO::FETCH_ASSOC);

        $affIds = [];
        foreach ($affiliateList as $affiliate) {
            $affIds[] = $affiliate["idrep"];
        }

        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM report_permissions WHERE user_id IN (";

        foreach ($affIds as $key => $id) {
            if ($key !== count($affIds) - 1) {
                $sql .= "?,";
            } else {
                $sql .= "?)";
            }
        }


        $prep = $db->prepare($sql);


        $prep->execute($affIds);

        $affs = $prep->fetchAll(\PDO::FETCH_ASSOC);

        $sorted = [];

        foreach ($affiliateList as $key => $aff) {
            if (in_array($aff["idrep"], $sorted) == false) {
                $array = [];
                $array["user_id"] = $aff["idrep"];
                $array["user_name"] = $aff["user_name"];

                foreach ($affs as $affPermissions) {
                    if ($affPermissions["user_id"] == $aff["idrep"]) {
                        foreach ($affPermissions as $permissionName => $val) {
                            if ($permissionName !== $array["user_id"]) {
                                $array[$permissionName] = $val;
                            }
                        }
                    }
                }


                $sorted[] = $array;
            }
        }


        return $sorted;
    }


    public function getPermissionListFromConstants()
    {
        try {
            $reflectionClass = new \ReflectionClass(new ReportPermissions());

            $this->permissionList = $reflectionClass->getConstants();
        } catch (\Exception $e) {

        }
    }

    public function isValidPermission($permission)
    {
        return in_array($permission, $this->permissionList);
    }

    public function saveFromPOST()
    {
        $db = DatabaseConnection::getInstance();

        $this->getPermissionListFromConstants();

        foreach ($_POST as $id => $val) {
            if (is_numeric($id)) {
                $sql = "UPDATE report_permissions SET ";
                $insertValues = [];

                foreach ($val as $permissionName => $pVal) {
                    if ($this->isValidPermission($permissionName)) {
                        $sql .= " {$permissionName} = ? ,";
                        $insertValues[] = $pVal;
                    }
                }

                $sql = substr($sql, 0, strlen($sql) - 1);

                $sql .= " WHERE user_id = ?";
                $insertValues[] = $id;


                $prep = $db->prepare($sql);

                $prep->execute($insertValues);

            }
        }


    }

    public function setUserPermission($permission, $allow)
    {
        if (isset($this->permissions[$permission])) {
            if ($allow) {
                $this->permissions[$permission] = 1;
            } else {
                $this->permissions[$permission] = 0;
            }
        }
    }


    public function updatePermissions()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "UPDATE report_permissions SET ";

        $insertValues = [];

        foreach ($this->permissions as $permission => $val) {
            if ($permission != "user_id") {
                $sql .= " ?,";
                $insertValues[] = $val;
            }
        }


        substr($sql, 0, strlen($sql) - 2);

        dd($sql);

        $sql .= " WHERE user_id = ?";
        $insertValues[] = $this->userId;

        $prep = $db->prepare($sql);

        return $prep->execute($insertValues);
    }


    public static function createPermissions($user_id)
    {
        $db = DatabaseConnection::getInstance();
        $sql = "INSERT IGNORE INTO report_permissions (user_id) VALUES(:user_id)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }


    public static function createPermissionsForAllAffiliates($dbCon)
    {
        $db = $dbCon;
        $affiliateList = User::selectAllAffiliateIDs($db)->fetchAll(\PDO::FETCH_OBJ);
        $affiliateList = multiDimentialToSingular($affiliateList);


        $sql = "INSERT IGNORE INTO report_permissions (user_id) VALUES ";
        $questionMarks = [];
        $insertValues = [];
        foreach ($affiliateList as $id) {
            $questionMarks[] = "(?)";
            $insertValues[] = $id;
        }

        $sql .= implode(",", $questionMarks);

        $prep = $db->prepare($sql);

        return $prep->execute($insertValues);
    }

    public function canSee($permission)
    {
        if ($permission == self::IGNORE) {
            return true;
        }

        if (isset($this->permissions[$permission])) {
            if ($this->permissions[$permission] == 1) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    public function loadUsersPermissions()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM report_permissions WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->userId);
        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $this->permissions = $result;
        }
    }


}