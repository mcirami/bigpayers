<?php namespace LeadMax\TrackYourStats\Report;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/18/2017
 * Time: 10:53 AM
 */

use Carbon\Carbon;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Table\ReportBase;
use PDO;

class AffiliatePayout
{

    public $affid = 0;


    public $dateFrom = "";

    public $dateTo = "";

    private $timezone = "America/New_York";

    public $conversions = array();

    public $bonuses = array();

    public $salaries = array();

    public $deductions = array();

    public $totalAll = 0;


    public $referrals = array();

    public function __construct($affid, $dateFrom, $dateTo )
    {
        $this->affid = $affid;

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

//        $this->dateToUnix($this->dateFrom, $timezone);
//        $this->dateToUnix($this->dateTo, $timezone);

    }

    private function dateToUnix($date, $timezone)
    {
        return Carbon::createFromFormat("Y-m-d H:i:s", $date, $timezone)->format("U");
    }


    public function toArray()
    {
        $report = [];

        $report['offer_revenue'] = $this->conversions['paid'];

        $report['salary'] = $this->salaries;

        $report['bonuses'] = $this->bonuses;

        $report['referrals'] = $this->referrals;

        $report['deductions'] = $this->deductions;

        $report['net'] = DollarSign::dollarSignNum($this->totalAll);

        return $report;
    }

    public function printReports()
    {

        $this->printConversions();

        $this->printSalaries();

        $this->printBonuses();

        $this->printReferrals();

        $this->printDeductions();

        $dollaDollaBillYall = ReportBase::dollarSignNum($this->totalAll);

        echo "<tr>";
        echo "<td><b>TOTAL</b>";
        echo "<td><b>Total All Revenue</b>";
        echo "<td><b>{$dollaDollaBillYall}</b></td>";
        echo "<td>N/A</td>";

        echo "</tr>";


    }

    public function fetchReports()
    {
        $this->conversions = $this->queryConversions()->fetch(PDO::FETCH_ASSOC);

        $this->bonuses = $this->queryBonuses()->fetchAll(PDO::FETCH_ASSOC);

        $this->salaries = $this->querySalaries()->fetchAll(PDO::FETCH_ASSOC);

        $this->referrals = $this->queryReferrals()->fetchAll(PDO::FETCH_ASSOC);

        $this->deductions = $this->queryDeductions()->fetch(PDO::FETCH_ASSOC);


    }

    public function processReports()
    {

        $this->processConversions();

        $this->processDeductions();

        $this->processBonuses();

        $this->processSalaries();

        $this->processReferrals();

    }

    private function processReferrals()
    {
        if (!empty($this->referrals)) {
            $total = 0;
            foreach ($this->referrals as &$referral) {

                $total += $referral["Referral_Revenue"];

                $referral["Referral_Revenue"] = ReportBase::dollarSignNum($referral["Referral_Revenue"]);


            }
            $this->referrals["total"] = ReportBase::dollarSignNum($total);
            $this->totalAll += $total;
        }
    }


    private function printReferrals()
    {
        if (!empty($this->referrals)) {


            foreach ($this->referrals as $referral => $val) {
                if ($referral !== "total") {
                    echo "<tr>";
                    echo "<td>Referral</td>";
                    echo "<td>{$val["user_name"]}</td>";
                    echo "<td>{$val["Referral_Revenue"]}</td>";
                    echo "<td>N/A</td>";
                    echo "</tr>";
                }
            }

            echo "<tr class='tr_row_space'>";
            echo "<td>Referral</td>";
            echo "<td><b>Total Referral Revenue</b></td>";
            echo "<td><b>{$this->referrals["total"]}</b></td>";
            echo "<td>N/A</td>";
            echo "</tr>";

        }
    }


    private function printSalaries()
    {
        if (!empty($this->salaries)) {

            foreach ($this->salaries as $salary => $val) {
                if ($salary !== "total") {
                    echo "<tr>";
                    echo "<td>Salary</td>";
                    echo "<td>{$val["reason"]}</td>";
                    echo "<td>{$val["payout"]}</td>";
                    echo "<td>{$val["timestamp"]}</td>";
                    echo "</tr>";
                }

            }
            echo "<tr class='tr_row_space'>";
            echo "<td>Salary</td>";
            echo "<td><b>Total Salary Revenue</b> </td>";
            echo "<td><b>{$this->salaries["total"]}</b></td>";
            echo "<td>N/A</td>";
            echo "</tr>";


        }
    }

    private function printBonuses()
    {
        if (!empty($this->bonuses)) {


            foreach ($this->bonuses as $bonus => $val) {
                if ($bonus !== "total") {
                    echo "<tr>";
                    echo "<td>Bonus</td>";
                    echo "<td>{$val["name"]}</td>";
                    echo "<td>{$val["payout"]}</td>";
                    echo "<td>{$val["timestamp"]}</td>";
                    echo "</tr>";
                }
            }
            echo "<tr class='tr_row_space'><td>Bonus</td>";
            echo "<td><b>Total Bonus Revenue</b></td>";

            echo "<td><b>{$this->bonuses["total"]}</b></td><td>N/A</td></tr>";


        }
    }

    private function printConversions()
    {
        if (!empty($this->conversions)) {


            echo "<tr class='tr_row_space'>";
            echo "<td>Offer</td>";
            echo "<td><b>Total Offer Revenue</b> </td>";
            echo "<td><b>{$this->conversions["paid"]}</b></td>";
            echo "<td>N/A</td>";

            echo "</tr>";


        }
    }


    private function printDeductions()
    {
        if (!empty($this->deductions) && isset($this->deductions["deductions"])) {


            echo "<tr class='tr_row_space'>";
            echo "<td>Deductions</td>";
            echo "<td><b>Total Deducted Offer Revenue</b> </td>";
            echo "<td><b>{$this->deductions["deductions"]}</b></td>";
            echo "<td>N/A</td>";

            echo "</tr>";


        }
    }

    private function processConversions()
    {
        if (empty($this->conversions)) {
            return;
        }

        $this->totalAll += $this->conversions["paid"];

        $this->conversions["paid"] = ReportBase::dollarSignNum($this->conversions["paid"]);
    }


    private function processDeductions()
    {
        if (empty($this->deductions) || !isset($this->deductions["deductions"])) {
            return;
        }


        $this->totalAll -= $this->deductions["deductions"];

        $this->deductions["deductions"] -= ($this->deductions["deductions"] * 2);

        $this->deductions["deductions"] = ReportBase::dollarSignNum($this->deductions["deductions"]);
    }

    private function processBonuses()
    {
        if (empty($this->bonuses)) {
            return;
        }

        $total = 0;
        foreach ($this->bonuses as &$bonus) {
            $total += $bonus["payout"];
            $bonus["payout"] = ReportBase::dollarSignNum($bonus["payout"]);
            $bonus["timestamp"] = Carbon::createFromFormat("U", $bonus["timestamp"])->toFormattedDateString();
        }

        $this->totalAll += $total;
        $this->bonuses["total"] = ReportBase::dollarSignNum($total);
    }


    private function processSalaries()
    {
        if (empty($this->salaries)) {
            return;
        }

        $total = 0;

        foreach ($this->salaries as &$salary) {
            $total += $salary["payout"];
            $salary["payout"] = ReportBase::dollarSignNum($salary["payout"]);
            $salary["timestamp"] = Carbon::createFromFormat("U", $salary["timestamp"])->toFormattedDateString();
        }

        $this->totalAll += $total;
        $this->salaries["total"] = ReportBase::dollarSignNum($total);

    }


    private function queryReferrals()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT rep.user_name, aff_id, sum(paid) as Referral_Revenue FROM referrals_paid INNER JOIN rep ON rep.idrep = aff_id WHERE referred_aff_id = :affid AND timestamp >= :dateFrom1 AND timestamp <= :dateTo1 GROUP BY aff_id
";


        $prep = $db->prepare($sql);


        $prep->bindParam(":dateFrom1", $this->dateFrom);

        $prep->bindParam(":dateTo1", $this->dateTo);

        $prep->bindParam(":affid", $this->affid);

        $prep->execute();

        return $prep;
    }


    private function querySalaries()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT payout, reason, salary_log.timestamp FROM salary_log INNER JOIN salary ON salary.id = salary_log.salary_id AND salary.user_id = :affid WHERE salary_log.timestamp >= :dateFrom AND salary_log.timestamp <= :dateTo";
        $prep = $db->prepare($sql);

        $prep->bindParam(":affid", $this->affid);

        $dateFrom = $this->dateToUnix($this->dateFrom, $this->timezone);
        $dateTo = $this->dateToUnix($this->dateTo, $this->timezone);

        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);

        $prep->execute();

        return $prep;
    }


    private function queryBonuses()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql =
            "SELECT name, click_bonus.payout, count(click_bonus.id) as timesBonusesHit , click_bonus.timestamp FROM click_bonus LEFT JOIN bonus ON bonus.id = click_bonus.bonus_id WHERE aff_id = :affid AND click_bonus.timestamp >= :dateFrom AND click_bonus.timestamp <= :dateTo GROUP BY bonus.id, click_bonus.id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $this->affid);


        $dateFrom = $this->dateToUnix($this->dateFrom, $this->timezone);
        $dateTo = $this->dateToUnix($this->dateTo, $this->timezone);

        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);
        $prep->execute();

        return $prep;
    }


    private function queryConversions()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT sum(paid) as paid FROM conversions WHERE user_id = :affid AND timestamp >= :dateFrom and timestamp <= :dateTo";
        $prep = $db->prepare($sql);


//        $dateFrom = Carbon::createFromFormat("U", $this->dateFrom)->format("Y-m-d H:i:s");
//        $dateTo = Carbon::createFromFormat("U", $this->dateTo)->format("Y-m-d H:i:s");

        $prep->bindParam(":affid", $this->affid);
        $prep->bindParam(":dateFrom", $this->dateFrom);
        $prep->bindParam(":dateTo", $this->dateTo);
        $prep->execute();

        return $prep;
    }

    private function queryDeductions()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT sum(paid) as deductions FROM conversions INNER JOIN deductions ON conversion_id = conversions.id AND deduction_timestamp BETWEEN :dateFrom AND :dateTo WHERE user_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->affid);
        $prep->bindParam(":dateFrom", $this->dateFrom);
        $prep->bindParam(":dateTo", $this->dateTo);
        $prep->execute();


        return $prep;
    }


}