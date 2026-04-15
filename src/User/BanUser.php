<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/14/2018
 * Time: 3:51 PM
 */

namespace LeadMax\TrackYourStats\User;


use LeadMax\TrackYourStats\Database\DatabaseConnection;

class BanUser
{


    public static function getBannedUsersQuery()
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM banned_users";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }

    public static function updateBan($user_id, $expires, $reason, $status)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE banned_users SET expires = :expires, reason = :reason, status = :status WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":expires", $expires);
        $prep->bindParam(":reason", $reason);
        $prep->bindParam(":status", $status);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }

    public static function doesUserHaveBanEntry($user_id)
    {
        return self::getBannedUserQuery($user_id)->rowCount() > 0;
    }

    public static function banUser($user_id, $expires, $reason)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO banned_users(user_id, expires, reason) VALUES(:user_id, :expires, :reason)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":expires", $expires);
        $prep->bindParam(":reason", $reason);

        if ($prep->execute()) {
            return User::updateUserStatus($user_id, 0);
        }

        return false;
    }

    public static function disableBan($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE banned_users SET status = 0 WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }

    public static function isUserBanned($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM banned_users WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        $prep->execute();

        $result = $prep->fetch(\PDO::FETCH_OBJ);

        if ($result == false) {
            return false;
        } else {
            if ($result->expires > date("Y-m-d H:i:s") && $result->status == 1) {
                return true;
            } else {
                return false;
            }
        }


    }

    public static function getBannedUserQuery($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM banned_users WHERE user_id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->execute();

        return $prep;
    }

}