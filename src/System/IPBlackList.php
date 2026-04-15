<?php namespace LeadMax\TrackYourStats\System;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/27/2017
 * Time: 12:01 PM
 */
class IPBlackList
{

    public $ip_address = "";

    public function __construct($ip_address)
    {
        $this->ip_address = ip2long($ip_address);
    }

    public static function selectIPs()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM ip_blacklist ORDER BY id DESC";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }

    public static function selectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM ip_blacklist WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);

        $prep->execute();

        return $prep;
    }

    public static function updateBlackList($id, $start, $end)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE ip_blacklist SET start = :start, end = :end WHERE id = :id";
        $prep = $db->prepare($sql);
        $start = ip2long($start);
        $end = ip2long($end);
        $prep->bindParam(":start", $start);
        $prep->bindParam(":end", $end);
        $prep->bindParam(":id", $id);

        return $prep->execute();
    }

    public static function deleteBlackList($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DELETE FROM ip_blacklist WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);

        return $prep->execute();
    }


    public static function createNewBlacklist($start, $end)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO ip_blacklist (start, end, timestamp) VALUES(:start, :end, :timestamp) ";
        $prep = $db->prepare($sql);
        $start = ip2long($start);
        $end = ip2long($end);
        $date = date("U");


        if ($start == false || $end == false) {
            return false;
        }

        $prep->bindParam(":start", $start);
        $prep->bindParam(":end", $end);
        $prep->bindParam(":timestamp", $date);

        return $prep->execute();
    }


    public function logIP()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO ip_blacklist_log (ip_address, timestamp) VALUES(:ip_address, :timestamp)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":ip_address", $this->ip_address);
        $timestamp = date("U");
        $prep->bindParam(":timestamp", $timestamp);

        return $prep->execute();
    }


    public function isBlackListed()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT id FROM ip_blacklist WHERE :ip >= start AND :ip2 <= end";
        $prep = $db->prepare($sql);
        $prep->bindParam(":ip", $this->ip_address);
        $prep->bindParam(":ip2", $this->ip_address);
        if ($prep->execute()) {
            if ($prep->rowCount() > 0) {
                return true;
            }

            return false;
        }

        return false;
    }


}