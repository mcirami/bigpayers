<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/24/2017
 * Time: 4:13 PM
 */

namespace LeadMax\TrackYourStats\Report;

use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\ReportBase;

// Offer Report
// Reports->Offer Report (nav bar)
class Offer extends ReportBase
{


    private $repType = -1;

    public $assign;

    public function __construct($repType, $assignments = false)
    {
        if ($assignments !== false) {
            $this->assign = $assignments;
        }

        $this->repType = $repType;
    }

    public function customEPC($report)
    {
        foreach ($report as $i => $val) {


            //EPC
            if ($report[$i]['UniqueClicks'] != 0) {
                $report[$i]['EPC'] = $this->roundEPC(($report[$i]['Revenue'] / $report[$i]['UniqueClicks']));
            } else {
                $report[$i]['EPC'] = 0;
            }


        }

        return $report;
    }


    public function process($noClicks)
    {
        if ($this->repType == \App\Privilege::ROLE_GOD || $this->repType == \App\Privilege::ROLE_ADMIN) {
            $this->processGod($noClicks);
        } else {
            $this->processManager($noClicks);
        }

    }

    public function report($items_per_page, $offset, $d_from = false, $d_to = false, $SELECT_NONACTIVE = false)
    {
        if ($this->repType == \App\Privilege::ROLE_GOD || $this->repType == \App\Privilege::ROLE_ADMIN) {
            $this->report = $this->reportGod($items_per_page, $offset, $d_from, $d_to,
                $SELECT_NONACTIVE)->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $this->report = $this->reportManager($items_per_page, $offset, $d_from, $d_to,
                $SELECT_NONACTIVE)->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function rowCount($d_from = false, $d_to = false, $SELECT_NONACTIVE = false, $noClicks = false)
    {
        if ($this->repType == \App\Privilege::ROLE_GOD || $this->repType == \App\Privilege::ROLE_ADMIN) {
            return $this->reportGod(false, false, $d_from, $d_to, $SELECT_NONACTIVE)->rowCount();
        } else {
            return $this->reportManager(false, false, $d_from, $d_to, $SELECT_NONACTIVE)->rowCount();
        }
    }


    //Managerial functions
    private function processManager($noClicks)
    {
        if ($noClicks != 1) {
            $report2 = array();
            foreach ($this->report as $row) {
                if ($row['Clicks'] != 0) {
                    array_push($report2, $row);
                }
            }
            $this->report = $report2;
        }

        $groupedReport = [];

        foreach ($this->report as $row) {
            $idOffer = $row["idoffer"];

            if (isset($groupedReport[$idOffer]) == false) {
                $groupedReport[$idOffer] = $row;

                if ($row["Revenue"] == null) {
                    $groupedReport[$idOffer]["Revenue"] = 0;
                }

            } else {
                $groupedReport[$idOffer]["Clicks"] += $row["Clicks"];
                $groupedReport[$idOffer]["UniqueClicks"] += $row["UniqueClicks"];
                $groupedReport[$idOffer]["Conversions"] += $row["Conversions"];
            }
        }

        $this->report = $groupedReport;


        $this->totalAll(['idoffer', 'offer_name']);

        $this->report = $this->customEPC($this->report);

        $this->dollarSignThese(['Revenue', 'EPC']);


        $this->addClickLinks();


    }

    private function addClickLinks()
    {
        foreach ($this->report as $key => &$offer) {
            if ($offer['idoffer'] != "TOTAL") {
                $offer['Clicks'] =
                    "<a href='/offer/{$offer['idoffer']}/clicks?d_from={$this->assign->get("d_from")}&d_to={$this->assign->get("d_to")}&dateSelect={$this->assign->get("dateSelect")}' name='offer{$offer['idoffer']}'>{$offer['Clicks']}</a>";
            }
        }
    }

    private function resetArrayKeys($array)
    {
        $temp = [];
        foreach ($array as $item) {
            $temp[] = $item;
        }

        return $temp;
    }

    public function printCustom()
    {
        $report = $this->resetArrayKeys($this->report);

        foreach ($report as $key => $offer) {
            if ($key == count($this->report) - 1) {
                echo "<tr class='static'>";
            } else {
                echo "<tr>";
            }
            echo "<td>{$offer["idoffer"]}</td>";
            echo "<td>{$offer["offer_name"]}</td>";
            echo "<td>{$offer["Clicks"]}</td>";
            echo "<td>{$offer["UniqueClicks"]}</td>";
            echo "<td>{$offer["free_sign_ups"]}</td>";
            echo "<td>{$offer["Conversions"]}</td>";
            echo "<td>{$offer["Revenue"]}</td>";
            echo "<td>{$offer["EPC"]}</td>";

            echo "</tr>";
        }
    }

    private function reportManager(
        $items_per_page = false,
        $offset = false,
        $d_from = false,
        $d_to = false,
        $SELECT_NONACTIVE = false
    ) {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT
            offer.idoffer,
            offer.offer_name, offer.offer_type,
            rep_has_offer.payout,
            (
            SELECT
                COUNT(*)
            FROM
                clicks
               
            WHERE
                clicks.offer_idoffer = rep_has_offer.offer_idoffer and clicks.rep_idrep = rep_has_offer.rep_idrep ";
        if ($d_from && $d_to) {
            $sql .= " AND  clicks.first_timestamp BETWEEN :f1 AND :t1 ";
        }


        $sql .= "
               ) AS Clicks,
                    (
                    SELECT
                        COUNT(*)
                    FROM
                        clicks
                    WHERE
                        clicks.offer_idoffer = offer.idoffer AND clicks.click_type = 0 AND clicks.rep_idrep = rep_has_offer.rep_idrep";

        if ($d_from && $d_to) {
            $sql .= " AND  clicks.first_timestamp BETWEEN :f3 AND :t3 ";
        }

        $sql .= " ) as UniqueClicks,
                
                (
                SELECT
                    COUNT(*)
                FROM
                    clicks
                    INNER JOIN conversions ON conversions.click_id = clicks.idclicks
                    ";
        if ($d_from && $d_to) {
            $sql .= "AND  conversions.timestamp BETWEEN :f2 AND :t2 ";
        }

        $sql .= "
                WHERE
                    clicks.offer_idoffer = offer.idoffer  AND clicks.rep_idrep = rep.idrep ";


        $sql .= ") AS Conversions,
             
                (SELECT count(*)
                	FROM clicks
                	INNER JOIN free_sign_ups ON free_sign_ups.click_id = clicks.idclicks AND free_sign_ups.timestamp BETWEEN :freeDateFrom AND :freeDateTo
               	WHERE clicks.offer_idoffer = offer.idoffer AND clicks.rep_idrep = rep.idrep)
               	 as free_sign_ups,
             
             
               ( SELECT
        sum(conversions.paid)
    FROM
        clicks
        LEFT JOIN rep ON rep.referrer_repid = :referrer_repid2
    INNER JOIN conversions
    ON conversions.click_id = clicks.idclicks AND clicks.rep_idrep = rep.idrep
           
          
           ";
        if ($d_from && $d_to) {
            $sql .= " AND  conversions.timestamp >= :f4 AND conversions.timestamp <= :t4 ";
        }


        $sql .= "
    
    WHERE
        clicks.offer_idoffer = offer.idoffer
          ) AS Revenue,

		(SELECT count(*) FROM free_sign_ups INNER JOIN clicks ON clicks.idclicks = click_id WHERE clicks.offer_idoffer = offer.idoffer AND free_sign_ups.timestamp BETWEEN :dateFrom5 AND :dateTo5) AS FreeSignUps

    
          
            FROM
                offer
             LEFT JOIN rep on  rep.referrer_repid = :referrer_repid
             
            LEFT JOIN rep_has_offer  ON rep_has_offer.offer_idoffer = offer.idoffer and rep_has_offer.rep_idrep = rep.idrep
           
                    
                    ";


        if ($SELECT_NONACTIVE) {
            $sql .= "WHERE offer.status = 0";
        } else {
            $sql .= "WHERE offer.status = 1";
        }

        $sql .= "
        

        ORDER BY
            Clicks
        DESC
            ,
            Conversions
        DESC";


        if ($items_per_page && $offset) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $stmt = $db->prepare($sql);


        $stmt->bindValue(":referrer_repid", Session::userID());
        $stmt->bindValue(":referrer_repid2", Session::userID());


        if ($d_from != false && $d_to != false) {

            $stmt->bindParam(":f1", $d_from);
            $stmt->bindParam(":f2", $d_from);
            $stmt->bindParam(":f3", $d_from);
            $stmt->bindParam(":f4", $d_from);
            $stmt->bindParam(":dateFrom5", $d_from);
            $stmt->bindParam(":freeDateFrom", $d_from);

            $stmt->bindParam(":t1", $d_to);
            $stmt->bindParam(":t2", $d_to);
            $stmt->bindParam(":t3", $d_to);
            $stmt->bindParam(":t4", $d_to);
            $stmt->bindParam(":dateTo5", $d_to);
            $stmt->bindParam(":freeDateTo", $d_to);
        }
        $stmt->execute();


        return $stmt;
    }

    //thy lord himself
    private function processGod($noClicks)
    {

        if ($noClicks == 0) {
            $report2 = array();
            foreach ($this->report as $row) {
                if ($row['Clicks'] != 0) {
                    array_push($report2, $row);
                }
            }
            $this->report = $report2;
        }


        foreach ($this->report as $key => &$val) {
            if (!isset($val["Revenue"])) {
                $val["Revenue"] = 0;
            }


        }


        $this->totalAll(['idoffer', 'offer_name']);

        $this->report = $this->customEPC($this->report);;

        $this->dollarSignThese(['Revenue', 'EPC']);

        $this->addClickLinks();

    }


    private function reportGod(
        $items_per_page = false,
        $offset = false,
        $d_from = false,
        $d_to = false,
        $SELECT_NONACTIVE = false
    ) {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql = "SELECT
                    offer.idoffer,
                    offer.offer_name, 
                    offer.offer_type,
                    offer.payout,
                  
                    (
                    SELECT
                        COUNT(*)
                    FROM
                        clicks
                    WHERE
                        clicks.offer_idoffer = offer.idoffer AND clicks.click_type != 2 ";
        if ($d_from && $d_to) {
            $sql .= " AND  clicks.first_timestamp >= :f1 AND  clicks.first_timestamp <= :t1 ";
        }


        $sql .= "
                ) AS Clicks,
                    (
                    SELECT
                        COUNT(*)
                    FROM
                        clicks
                    WHERE
                        clicks.offer_idoffer = offer.idoffer AND clicks.click_type = 0 ";

        if ($d_from && $d_to) {
            $sql .= " AND  clicks.first_timestamp >=  :f3 AND  clicks.first_timestamp <= :t3 ";
        }

        $sql .= " ) as UniqueClicks,
                
                (SELECT
                    COUNT(*)
                FROM
                    clicks
                INNER JOIN conversions ON conversions.click_id = clicks.idclicks ";

        if ($d_from && $d_to) {
            $sql .= "AND  conversions.timestamp >= :f2 AND  conversions.timestamp <= :t2 ";
        }

        $sql .= "
                WHERE
                    clicks.offer_idoffer = offer.idoffer    ";


        $sql .= "
                
                ) AS Conversions,
                
                
                (SELECT count(*)
                	FROM clicks
                	INNER JOIN free_sign_ups ON free_sign_ups.click_id = clicks.idclicks AND free_sign_ups.timestamp BETWEEN :freeDateFrom AND :freeDateTo
               	WHERE clicks.offer_idoffer = offer.idoffer )
               	 as free_sign_ups,
                
                
                
                
                
             ( SELECT
        sum(conversions.paid)
    FROM
       conversions 
    INNER JOIN clicks 
    ON conversions.click_id = clicks.idclicks ";

        if ($d_from && $d_to) {
            $sql .= " AND  conversions.timestamp >= :f4 AND conversions.timestamp <= :t4 ";
        }

        $sql .= "
    WHERE
        clicks.offer_idoffer = offer.idoffer  ";


        $sql .= " ) AS Revenue,
		(SELECT count(*) FROM free_sign_ups INNER JOIN clicks ON clicks.idclicks = click_id WHERE clicks.offer_idoffer = offer.idoffer AND free_sign_ups.timestamp BETWEEN :dateFrom5 AND :dateTo5) AS FreeSignUps
                FROM
                    offer
              ";

        if ($SELECT_NONACTIVE) {
            $sql .= "WHERE offer.status = 0";
        } else {
            $sql .= "WHERE offer.status = 1";
        }

        $sql .= "
                ORDER BY
                    Clicks
                DESC
                    ,
                    Conversions
                DESC ";


        if ($items_per_page && $offset) {
            $sql .= "LIMIT {$items_per_page} ";
            $sql .= "OFFSET {$offset}";
        }


        $stmt = $db->prepare($sql);


        if ($d_from != false && $d_to != false) {

            $stmt->bindParam(":f1", $d_from);
            $stmt->bindParam(":f2", $d_from);
            $stmt->bindParam(":f3", $d_from);
            $stmt->bindParam(":f4", $d_from);
            $stmt->bindParam(":dateFrom5", $d_from);
            $stmt->bindParam(":freeDateFrom", $d_from);

            $stmt->bindParam(":t1", $d_to);
            $stmt->bindParam(":t2", $d_to);
            $stmt->bindParam(":t3", $d_to);
            $stmt->bindParam(":t4", $d_to);
            $stmt->bindParam(":dateTo5", $d_to);
            $stmt->bindParam(":freeDateTo", $d_to);
        }
        $stmt->execute();

        return $stmt;
    }


}
