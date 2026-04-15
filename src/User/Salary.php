<?php namespace LeadMax\TrackYourStats\User;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/9/2017
 * Time: 11:21 AM
 */

use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use PDO;

class Salary
{


    public $affiliateList = array();

    public $affid = 0;

    public $affiliateData = array();

    public function __construct($affiliate_id = false)
    {
        if ($affiliate_id) {
            $this->affid = $affiliate_id;
            $this->affiliateData = $this->queryAffiliateSalary()->fetch(PDO::FETCH_ASSOC);
        }
    }


    public function hasSalary()
    {
        if ($this->affiliateData == false) {
            return false;
        }

        if (empty($this->affiliateData)) {
            return false;
        }


        return true;
    }


    public function fetchAffiliateSalaries()
    {
        $this->affiliateList = $this->queryFetchAffiliateSalaries()->fetchAll(PDO::FETCH_ASSOC);
    }


    public function queryFetchAffiliateSalaries()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name, salary.id, salary.status, salary.last_update, salary FROM salary RIGHT JOIN rep ON rep.idrep = salary.user_id AND rep.lft > :left AND rep.rgt < :right INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1 ";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;
    }


    private function queryAffiliateSalary()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT salary, last_update, salary.status, rep.user_name FROM salary RIGHT JOIN rep ON rep.idrep = salary.user_id WHERE user_id = :affiliate_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affiliate_id", $this->affid);
        $prep->execute();

        return $prep;
    }


    /* INPUT:
           array = ( 5 => ['salary_id' => 2, 'payout'=> 400, 'reason' => 'Didn\'t work this week'] )
    etc..
     */

    public function payAllAffiliates($affiliateList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $questionMarks = array();
        $insertValues = array();
        foreach ($affiliateList as $user_id => $vals) {
            $questionMarks[] = "(?, ?, ?, ?)";
            $insertValues[] = $vals["salary_id"];
            $insertValues[] = $vals["payout"];
            $insertValues[] = $vals["reason"];
            $insertValues[] = date("U");

        }

        $sql = "INSERT INTO salary_log (salary_id, payout, reason, timestamp) VALUES".implode(",", $questionMarks);
        $prep = $db->prepare($sql);

        return $prep->execute($insertValues);
    }

    public function payAffiliate($user_id, $payout, $reason)
    {
        // find salary.id
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT id FROM salary WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->execute();
        $salary_id = $prep->fetch(PDO::FETCH_ASSOC)["id"];


        // was this affiliate already paid this week
        $sql = "SELECT * FROM salary_log WHERE salary_id = :salary_id AND timestamp >= :start AND timestamp <= :end";
        $prep = $db->prepare($sql);
        $date = Date::getSalesWeekEpoch();
        $prep->bindParam(":salary_id", $salary_id);
        $prep->bindParam(":start", $date["start"]);
        $prep->bindParam(":end", $date["end"]);
        $prep->execute();
        if ($prep->rowCount() > 0) {
            return false;
        }


        //pay that slacker
        $sql = "INSERT INTO salary_log(salary_id, payout, reason, timestamp) VALUES(:salary_id, :payout, :reason, :timestamp)";

        $prep = $db->prepare($sql);
        $prep->bindParam(":salary_id", $salary_id);
        $prep->bindParam(":payout", $payout);
        $prep->bindParam(":reason", $reason);
        $date = date("U");
        $prep->bindParam(":timestamp", $date);

        return $prep->execute();
    }


    //pulls all paid logs for this week (monday - sunday)

    public function weekReport()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name, salary, salary.id, salary.status, last_update, salary_log.payout, salary_log.reason FROM rep LEFT JOIN salary ON salary.user_id = rep.idrep LEFT JOIN salary_log ON salary_log.salary_id = salary.id 

AND salary_log.timestamp >= :monday AND salary_log.timestamp <= :sunday

          WHERE rep.lft > :left AND rep.rgt < :right ";


        $prep = $db->prepare($sql);
        $date = Date::getSalesWeekEpoch();
        $prep->bindParam(":monday", $date["start"]);
        $prep->bindParam(":sunday", $date["end"]);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;
    }


    public static function createSalary($userID, $salary)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO salary (user_id, salary, timestamp, last_update, status) VALUES(:userID, :salary, :timestamp, :last_update, 1)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":userID", $userID);
        $prep->bindParam(":salary", $salary);

        $date = date("U");
        $prep->bindParam(":timestamp", $date);
        $prep->bindParam(":last_update", $date);


        return $prep->execute();
    }


    public static function updateSalary($user_id, $salary, $status)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE salary SET salary = :salary, status = :status WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":salary", $salary);
        $prep->bindParam(":status", $status);

        return $prep->execute();
    }


    public static function disableSalary()
    {

    }

}