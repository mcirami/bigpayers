<?php

namespace LeadMax\TrackYourStats\Report;

// Affiliate Level Report (the report a logged in affiliate will see)
// Reports->Report (nav bar)
use Carbon\Carbon;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\Table\ReportBase;
use LeadMax\TrackYourStats\User\ReportPermissions;
use PDO;

class Affiliate extends ReportBase
{

    public $bonuses = array();

    public $salary = array();

    public $totalAll = 0;

    public $reportPermissions;

    private $headers = [
        ReportPermissions::OFFER_ID => "Offer ID",
        ReportPermissions::OFFER_NAME => "Offer Name",
        ReportPermissions::RAW_CLICKS => "Raw",
        ReportPermissions::UNIQUE_CLICKS => "Unique",
        ReportPermissions::FREE_SIGN_UPS => "Free Sign Ups",
        ReportPermissions::CONVERSIONS => "Conversions",
        ReportPermissions::REVENUE => "Revenue",
        ReportPermissions::EPC => "EPC",
        ReportPermissions::IGNORE => "TOTAL",
    ];


    function __construct()
    {
    }

    private function loadReportPermissions()
    {
        $this->reportPermissions = new ReportPermissions(Session::userID());
    }

    // wrapper for ReportPermissions class
    private function canSee($permission)
    {
        return $this->reportPermissions->canSee($permission);
    }


    public function printHeaders($ignorePermissions = false)
    {
        $this->loadReportPermissions();

        foreach ($this->headers as $permission => $header) {
            if ($ignorePermissions) {
                echo "<th class='value_span9'>$header</th>";
            } else {
                if ($this->canSee($permission)) {
                    echo "<th class='value_span9'>$header</th>";
                }
            }
        }

    }

    function rowCount($d_from, $d_to, $SELECT_NONACTIVE = false)
    {
        return $this->report(false, false, $d_from, $d_to, $SELECT_NONACTIVE, true);
    }


    public function reportPrint($ignorePermissions = false)
    {
        foreach ($this->report as $key => $row) {
            if ($key !== count($this->report) - 1) {
                echo "<tr>";
            } else {
                echo "<tr class='static'>";
            }

            foreach ($this->headers as $permission => $valueidontcareabout) {
                if ($ignorePermissions) {
                    if ($valueidontcareabout == "TOTAL") {
                        echo "<td>{$row["TOTAL"]}</td>";
                    } else {
                        echo "<td>{$row[$permission]}";
                    }
                } else {
                    if ($this->canSee($permission)) {
                        if ($valueidontcareabout == "TOTAL") {
                            echo "<td>{$row["TOTAL"]}</td>";
                        } else {
                            echo "<td>{$row[$permission]}";
                        }
                    }
                }
            }
            echo "</tr>";
        }


    }


    public function fetchSalary($dateFrom, $dateTo)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT payout, reason, salary.timestamp FROM salary_log INNER JOIN salary ON salary.id = salary_log.salary_id AND salary.user_id = :affiliate_id WHERE salary_log.timestamp >= :dateFrom and salary_log.timestamp <= :dateTo";
        $prep = $db->prepare($sql);

        $userID = Session::userID();
        $prep->bindParam(":affiliate_id", $userID);

        $dateFromU = Carbon::createFromFormat("Y-m-d", $dateFrom)->format("U");
        $dateToU = Carbon::createFromFormat("Y-m-d", $dateTo)->format("U");
        $prep->bindParam(":dateFrom", $dateFromU);
        $prep->bindParam(":dateTo", $dateToU);

        $prep->execute();

        $this->salary = $prep->fetchAll(PDO::FETCH_ASSOC);
    }


    public function printBonuses()
    {
        if (!$this->bonuses) {
            return;
        }

        $temp = $this->totalAllCustom($this->bonuses, ['name']);
        $this->totalAll += $temp[(count($temp) - 1)]["payout"];

        $temp = $this->dollarSignTheseCustom($temp, ['payout']);
        foreach ($temp as $bonus) {
            echo "<tr>";
            echo "<td>{$bonus["name"]}</td>";
            echo "<td>{$bonus["payout"]}</td>";
            echo "</tr>";
        }
    }

    public function fetchBonuses($dateFrom, $dateTo)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT bonus.name, click_bonus.payout FROM click_bonus INNER JOIN bonus ON bonus.id = click_bonus.bonus_id WHERE aff_id = :user_id AND click_bonus.timestamp >= :startDate AND click_bonus.timestamp <= :endDate";
        $prep = $db->prepare($sql);

        $dateFrom = Date::convertTimestampToEpoch($dateFrom);
        $dateTo = Date::convertTimestampToEpoch($dateTo);

        $userID = Session::userID();
        $prep->bindParam(":user_id", $userID);
        $prep->bindParam(":startDate", $dateFrom);
        $prep->bindParam(":endDate", $dateTo);
        $prep->execute();
        $this->bonuses = $prep->fetchAll(PDO::FETCH_ASSOC);
    }

    function report(
        $items_per_page = false,
        $offset = false,
        $d_from,
        $d_to,
        $SELECT_NONACTIVE = false,
        $rowCount = false
    ) {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $sql = "SELECT
                    offer.idoffer as offer_id,
                    offer.offer_name as offer_name,
                   
                  
                    (
                    SELECT
                        COUNT(*)
                    FROM
                        clicks
                    WHERE
                        clicks.offer_idoffer = offer.idoffer AND clicks.rep_idrep = :repID3 AND clicks.click_type != 2";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from AND clicks.first_timestamp <=  :d_to  ";
        }


        $sql .= "
                ) AS raw_clicks,
                
                  (
                    SELECT
                        COUNT(*)
                    FROM
                        clicks
                    WHERE
                        clicks.offer_idoffer = offer.idoffer AND clicks.rep_idrep = :repID4 AND clicks.click_type = 0";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from4 AND clicks.first_timestamp <=  :d_to4 ";
        }

        $sql .= " ) as unique_clicks,
		
		(SELECT count(*) FROM clicks INNER JOIN free_sign_ups ON free_sign_ups.click_id = clicks.idclicks AND free_sign_ups.timestamp BETWEEN :dateFrom4 AND :dateTo4 WHERE clicks.offer_idoffer = offer.idoffer AND clicks.rep_idrep = :repID6) as free_sign_ups,
                (SELECT
                    COUNT(conversions.click_id)
                FROM
                  clicks  
               INNER JOIN conversions ON clicks.idclicks = conversions.click_id 
               ";
        if ($d_from && $d_to) {
            $sql .= " AND conversions.timestamp >= :d_from2 AND conversions.timestamp <=  :d_to2   ";
        }

        $sql .= " WHERE clicks.rep_idrep = :repID5 AND clicks.offer_idoffer = offer.idoffer";


        $sql .= "
                
                ) AS conversions,
                
                   (SELECT  SUM(conversions.paid) FROM clicks
                INNER JOIN conversions ON conversions.click_id = clicks.idclicks";

        if ($d_from && $d_to) {
            $sql .= "  AND conversions.timestamp >= :d_from3 AND conversions.timestamp <=  :d_to3   ";
        }

        $sql .= " WHERE clicks.offer_idoffer = offer.idoffer AND clicks.rep_idrep = :repID2";


        $sql .= ")  as revenue
		
                
                
                FROM
                    offer
                INNER JOIN rep_has_offer
                ON rep_has_offer.rep_idrep = :repID and offer.idoffer = rep_has_offer.offer_idoffer
                
     

                ";

        if ($SELECT_NONACTIVE) {
            $sql .= "WHERE offer.status = 0";
        } else {
            $sql .= "WHERE offer.status = 1";
        }


        $sql .= "
                GROUP BY offer.idoffer, rep_has_offer.rep_idrep
        
                ORDER BY
                   	raw_clicks
                DESC,
                   unique_clicks
                DESC,
                    
                    conversions
                DESC ";

        if ($items_per_page && $offset) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $stmt = $db->prepare($sql);
        $userID = Session::userID();

        $stmt->bindParam(":repID", $userID);
        $stmt->bindParam(":repID2", $userID);
        $stmt->bindParam(":repID3", $userID);
        $stmt->bindParam(":repID4", $userID);
        $stmt->bindParam(":repID5", $userID);
        $stmt->bindParam(":repID6", $userID);

        if ($d_from != false && $d_to != false) {

            $stmt->bindParam(":d_from", $d_from);
            $stmt->bindParam(":d_from2", $d_from);
            $stmt->bindParam(":d_from3", $d_from);
            $stmt->bindParam(":d_from4", $d_from);
            $stmt->bindParam(":dateFrom4", $d_from);

            $stmt->bindParam(":d_to", $d_to);
            $stmt->bindParam(":d_to2", $d_to);
            $stmt->bindParam(":d_to3", $d_to);
            $stmt->bindParam(":d_to4", $d_to);
            $stmt->bindParam(":dateTo4", $d_to);


        }
        $stmt->execute();


        if (!$rowCount) {
            $this->fetchBonuses($d_from, $d_to);
            $this->report = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $this->report = $stmt->rowCount();
        }


    }

    function EPC()
    {
        for ($i = 0; $i < count($this->report); $i++) {


            //EPC
            if ($this->report [$i]['unique_clicks'] != 0) {
                $this->report [$i]['epc'] = $this->roundEPC(($this->report [$i]['revenue'] / $this->report [$i]['unique_clicks']));
            } else {
                $this->report [$i]['epc'] = 0;
            }

            // removed..
            //allows rep to look at their sepcific clicks
        }
    }

    function process()
    {

        // remove offers that don't have any clicks..
        $report2 = array();
        foreach ($this->report as $row) {
            if ($row['raw_clicks'] != 0) {
                array_push($report2, $row);
            }
        }
        $this->report = $report2;


        foreach ($this->report as $key => $row) {
            if (!isset($row['revenue'])) {
                $this->report [$key]["revenue"] = 0;
            }


            if (!isset($this->report[$key]["TOTAL"])) {
                $this->report[$key]["TOTAL"] = 0;
            }

            $this->report[$key]["TOTAL"] += $row["revenue"];


        }

        $this->totalAll(['offer_id', 'offer_name']);

        if (count($this->report) !== 0) {
            $this->totalAll += $this->report[count($this->report) - 1]["TOTAL"];
        }


        $this->EPC();

        $this->dollarSignThese(['revenue', 'epc', 'TOTAL']);


    }


}
