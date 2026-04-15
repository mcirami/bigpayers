<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/5/2018
 * Time: 9:48 AM
 */

namespace LeadMax\TrackYourStats\Offer;


use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Session;
use \LeadMax\TrackYourStats\User\User;

class SaleLog
{
    public $id;

    public $conversion_id;

    public $timestamp;

    private $saleLogObj;


    public function verifyAffiliateOwnsSaleLog($userId, $saleLogId)
    {
        $this->selectSaleLog($saleLogId);

        if ($this->verifySaleIdExists()) {
            return Conversion::doesUserOwnConversion($userId, $this->saleLogObj->conversion_id);
        } else {
            return false;
        }
    }


    public function verifyLoggedInUserOwnsSaleLog($saleLogId)
    {
        switch (Session::userType()) {
            case \App\Privilege::ROLE_GOD:
                return true;

            case \App\Privilege::ROLE_ADMIN:
            case \App\Privilege::ROLE_MANAGER:
                return $this->verifyManagerOwnsSaleLog(Session::userID(), $saleLogId);

            case \App\Privilege::ROLE_AFFILIATE:
                return $this->verifyAffiliateOwnsSaleLog(Session::userID(), $saleLogId);
        }
    }

    public function verifyManagerOwnsSaleLog($userId, $saleLogId)
    {
        $this->selectSaleLog($saleLogId);

        if ($this->verifySaleIdExists()) {
            $conversion = Conversion::selectOneByConversionID($this->saleLogObj->conversion_id);

            return User::userOwnsUser($userId, $conversion->user_id);
        } else {
            return false;
        }
    }

    public function selectSaleLog($id)
    {
        $this->saleLogObj = self::selectOneQuery($id)->fetch(\PDO::FETCH_OBJ);
    }

    public function verifySaleIdExists()
    {
        if ($this->saleLogObj) {
            return true;
        } else {
            return false;
        }
    }

    public static function getImageURLsFromSaleId($sale_id)
    {
        $files = scandir(env('SALE_LOG_DIRECTORY').'/'.Company::loadFromSession()->getSubDomain().'/'.$sale_id);

        $filtered = [];

        foreach ($files as $fileName) {
            if ($fileName !== "." && $fileName !== "..") {
                $filtered[] = $fileName;
            }
        }

        return $filtered;
    }

    public static function selectOneFromConversionIdQuery($conversion_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM sale_log WHERE conversion_id = :conversion_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $conversion_id);
        $prep->execute();

        return $prep;
    }

    public static function selectOneQuery($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM sale_log WHERE id = :id";
        $prep = $db->prepare($sql);

        $prep->bindParam(":id", $id);

        $prep->execute();

        return $prep;
    }


    public function save()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT IGNORE INTO sale_log (conversion_id) VALUES(:conversion_id)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $this->conversion_id);

        if ($prep->execute()) {
            $this->id = $db->lastInsertId();

            return true;
        } else {
            return false;
        }
    }

    public function renameSaleImage($fileName)
    {
        return unlink(env('SALE_LOG_DIRECTORY')."/$this->id/{$fileName}");
    }

}