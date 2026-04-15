<?php namespace LeadMax\TrackYourStats\User;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 9/28/2017
 * Time: 3:47 PM
 */

/*
    DB TABLE 'bonus'
    ----------------
    id
    name
    sales_required
    payout
    author
    is_active
    ----------------
*/

use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use PDO;

class Bonus
{

    public $userID = -1;

    public $bonuses = array();

    public $completedBonuses = array();

    public $availableBonuses = array();

    public $newAchievedBonuses = array();

    public $sales = 0;

    public $dateFrom;

    public $dateTo;

    public function __construct($userID = false, $editing = false)
    {
        if ($userID) {
            $this->userID = $userID;

            if (Session::userType() == \App\Privilege::ROLE_GOD && $editing) {

                $this->bonuses = $this->querySelectAllBonuses()->fetchAll(PDO::FETCH_ASSOC);
            } else {

                $this->bonuses = $this->queryUsersBonuses()->fetchAll(PDO::FETCH_ASSOC);
            }
        }

    }

    public static function registerBonusToUser($bonusId, $userId)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $bonus = self::SelectOne($bonusId)->fetch(PDO::FETCH_OBJ);


        if ($bonus == false || $bonus->is_active != 1) {
            return false;
        }

        $timestamp = date("U");
        $sql = "INSERT INTO click_bonus(bonus_id, aff_id, timestamp, payout) VALUES(:bonus_id, :user_id, :timestamp, :payout)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":bonus_id", $bonusId);
        $prep->bindParam(":user_id", $userId);
        $prep->bindParam(":timestamp", $timestamp);
        $prep->bindParam(":payout", $bonus->payout);

        return $prep->execute();
    }


    public function setCustomDateRangeForBonusCheck($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public static function findAssignedUsers($userList, $assignedUsers)
    {
        $filteredAssignedUsers = array();

        foreach ($userList as $key => $val) {
            foreach ($assignedUsers as $user) {
                if ($user["idrep"] == $val["idrep"]) {
                    if (!in_array($val, $filteredAssignedUsers)) {
                        array_push($filteredAssignedUsers, $val);
                        unset($userList[$key]);
                    }
                }
            }
        }

        return ["userList" => $userList, "assignedUsers" => $filteredAssignedUsers];
    }


    public static function queryFindAssignedUsers($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN user_has_bonus ON user_id = idrep AND bonus_id = :id WHERE lft > :left AND rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $left = Session::userData()->lft;
        $right = Session::userData()->rgt;
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->execute();

        return $prep;
    }

    public static function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM bonus WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->execute();

        return $prep;
    }

    public static function querySelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        if (Session::userType() == \App\Privilege::ROLE_GOD) {
            $sql = "SELECT * FROM bonus WHERE id = :id";
        } else {
            $sql = "SELECT * FROM bonus INNER JOIN user_has_bonus ON bonus_id = :bonus_id AND user_id = :user_id WHERE id = :id";
        }

        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);

        if (Session::userType() != \App\Privilege::ROLE_GOD) {
            $prep->bindParam(":bonus_id", $id);

            $userID = Session::userID();
            $prep->bindParam(":user_id", $userID);
        }

        $prep->execute();

        return $prep;
    }

    public function checkForAchievedBonuses()
    {
        foreach ($this->availableBonuses as $bonus) {
            if ($this->sales >= $bonus["sales_required"]) {
                array_push($this->newAchievedBonuses, $bonus);
            }
        }
    }

    public function registerAchievedBonuses()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        foreach ($this->newAchievedBonuses as $bonus) {
            $date = date("U");


            $sql = "INSERT INTO click_bonus (bonus_id, aff_id, timestamp, payout) VALUES(:bonus_id, :aff_id, :timestamp, :payout)";
            $prep = $db->prepare($sql);
            $prep->bindParam(":bonus_id", $bonus["id"]);
            $prep->bindParam(":aff_id", $this->userID);
            $prep->bindParam(":timestamp", $date);
            $prep->bindParam(":payout", $bonus["payout"]);
            $prep->execute();


            $click_bonus_id = $db->lastInsertId();

            $totalCompletedSales = $this->queryGetSales()->fetchAll(PDO::FETCH_ASSOC);

            $questionMarks = array();
            $insertValues = array();
            foreach ($totalCompletedSales as $conversion) {
                $questionMarks[] = "(?,?)";
                $insertValues[] = $click_bonus_id;
                $insertValues[] = $conversion["click_id"];
            }

            $sql = "INSERT INTO click_has_bonus (click_bonus_id, click_id) VALUES ".implode(",", $questionMarks);
            $prep = $db->prepare($sql);
            $prep->execute($insertValues);

        }


    }

    public function processAll()
    {
        $this->getSalesCount();

        $this->getCompletedBonuses();

        $this->findAvailableBonuses();

        $this->checkForAchievedBonuses();

        $this->registerAchievedBonuses();

    }


    private function findAvailableBonuses()
    {
        $this->availableBonuses = $this->bonuses;
        foreach ($this->completedBonuses as $cBonus) {
            foreach ($this->availableBonuses as $key => $bonus) {
                if ($cBonus["bonus_id"] == $bonus["id"]) {
                    unset($this->availableBonuses[$key]);
                }
            }
        }

        // remove in-active bonuses
        foreach ($this->availableBonuses as $key => $bonus) {
            if ($bonus["is_active"] == 0) {
                unset($this->availableBonuses[$key]);
            }
        }


    }


    private function getCompletedBonuses()
    {
        $currentWeek = Date::getSalesWeekEpoch();
        $user_id = $this->userID;

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM click_bonus WHERE aff_id = :user_id AND click_bonus.timestamp >= :start_date AND click_bonus.timestamp <= :end_date";

        $prep = $db->prepare($sql);

        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":start_date", $currentWeek["start"]);
        $prep->bindParam(":end_date", $currentWeek["end"]);

        $prep->execute();

        $this->completedBonuses = $prep->fetchAll(PDO::FETCH_ASSOC);

    }

    private function getSalesCount()
    {
        $this->sales = $this->queryGetSales()->rowCount();
    }

    public function queryGetSales()
    {
        $currentWeek = Date::getSalesWeek();
        $user_id = $this->userID;

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM conversions WHERE user_id = :user_id AND timestamp >= :start_date AND timestamp <= :end_date";

        $prep = $db->prepare($sql);

        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":start_date", $currentWeek["start"]);
        $prep->bindParam(":end_date", $currentWeek["end"]);

        $prep->execute();

        return $prep;
    }


    private function querySelectAllBonuses()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM bonus";
        $prep = $db->prepare($sql);
        $prep->execute();

        return $prep;
    }


    private function queryUsersBonuses()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM bonus INNER JOIN user_has_bonus ON user_id = :userID  AND bonus_id = bonus.id GROUP BY bonus.id, user_has_bonus.user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":userID", $this->userID);
        $prep->execute();

        return $prep;
    }

    public static function createBonus($name, $sales_required, $payout, $status, $inheritable)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO bonus (name, sales_required, payout, author, is_active, timestamp, inheritable) VALUES(:name, :sales_required, :payout, :author, :status, :timestamp, :inheritable)";
        $prep = $db->prepare($sql);

        $prep->bindParam(":name", $name);
        $prep->bindParam(":sales_required", $sales_required);
        $prep->bindParam(":payout", $payout);
        $prep->bindParam(":status", $status);

        $date = date("U");
        $prep->bindParam(":timestamp", $date);

        $prep->bindParam(":inheritable", $inheritable);

        $userID = Session::userID();
        $prep->bindParam(":author", $userID);

        if ($prep->execute()) {
            return $db->lastInsertId();
        }

        return false;

    }

    public static function updateBonus($id, $name, $sales_required, $payout, $status, $inheritable)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE bonus SET name = :name, sales_required = :sales_required, payout = :payout, is_active = :status, inheritable = :inheritable WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":name", $name);
        $prep->bindParam(":sales_required", $sales_required);
        $prep->bindParam(":payout", $payout);
        $prep->bindParam(":status", $status);
        $prep->bindParam(":id", $id);
        $prep->bindParam(":inheritable", $inheritable);

        return $prep->execute();
    }

    public static function disableBonus($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE bonus SET status = 0 WHERE id = :id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);

        if ($prep->execute()) {
            return true;
        }

        return false;
    }

    public static function queryFetchUsersBonuses($userId)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM user_has_bonus INNER JOIN bonus ON bonus_id = bonus.id WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $userId);
        $prep->execute();

        return $prep;
    }


    public static function assignUsersInheritableBonuses(array $userIds, $referredUserId)
    {
        $bonuses = self::queryFetchUsersBonuses($referredUserId)->fetchAll(PDO::FETCH_OBJ);

        foreach ($bonuses as $bonus) {
            if ($bonus->inheritable == 1) {
                self::assignUsersToBonus($bonus->id, $userIds);
            }
        }

    }

    public static function assignUsersToBonus($bonusID, $userList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $insertValues = array();
        $questionMarks = array();

        foreach ($userList as $id) {
            $questionMarks[] = "(?,?)";
            $insertValues[] = $bonusID;
            $insertValues[] = $id;
        }

        $sql = "INSERT IGNORE INTO user_has_bonus (bonus_id, user_id) VALUES ".implode(",", $questionMarks);
        $prep = $db->prepare($sql);

        if ($prep->execute($insertValues)) {
            return true;
        }

        return false;
    }


    public static function removeUsersFromBonus($bonusID, $userList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DELETE FROM user_has_bonus WHERE bonus_id = ? AND user_id = ";

        for ($i = 0; $i < count($userList); $i++) {
            if ($i == 0) {
                $sql .= " ? ";
            } else {
                if ($i != count($userList)) {
                    $sql .= "  OR user_id = ? ";
                } else {
                    $sql .= " OR user_id =  ? ";
                }
            }

        }


        $userList = array_merge([$bonusID], $userList);


        $prep = $db->prepare($sql);

        if ($prep->execute($userList)) {
            return true;
        }

        return false;


    }

}

