<?php

namespace LeadMax\TrackYourStats\Report\ID;


// User specific Click Report

use Carbon\Carbon;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\Clicks\ClickVars;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\ReportBase;
use LeadMax\TrackYourStats\User\Permissions;
use PDO;

class Clicks extends ReportBase
{

    public $userType = -1;

    public $report = array();

    public $assign;


    function __construct($usrType, $assignments = false)
    {
        if ($assignments !== false) {
            $this->assign = $assignments;
        }


        $this->userType = $usrType;
    }


    public function getCount($d_from = false, $d_to = false, $repID)
    {
        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
            return $this->queryAffiliate($d_from, $d_to, $repID, 0, 0, true)->rowCount();
        } else {
            return $this->queryEmployee($d_from, $d_to, $repID, 0, 0, true)->rowCount();
        }
    }


    private function queryEmployee($d_from = false, $d_to = false, $repID, $rowCount = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $sql1 = "SELECT lft, rgt FROM rep WHERE idrep = :repIDjuan";

        $prepz = $db->prepare($sql1);

        $prepz->bindParam(":repIDjuan", $repID);
        $prepz->execute();


        $rep = $prepz->fetch(\PDO::FETCH_ASSOC);

        $per = Permissions::loadFromSession();

        if ($per->can("view_fraud_data")) {
            $sql = "SELECT  clicks.idclicks, clicks.first_timestamp,  conversions.timestamp, conversions.paid, click_vars.url,  click_geo.ip, rep.idrep, rep.first_name, clicks.offer_idoffer FROM clicks
                    INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks
                    INNER JOIN click_geo ON click_geo.click_id = clicks.idclicks
                    LEFT JOIN conversions ON conversions.click_id = clicks.idclicks                    ";
        } else {
            $sql = "SELECT clicks.first_timestamp,  conversions.timestamp,conversions.paid,  click_vars.url,  click_geo.ip, rep.idrep, rep.first_name, clicks.offer_idoffer FROM clicks
                    INNER JOIN click_vars ON click_vars.click_id = clicks.idclicks
                    INNER JOIN click_geo ON click_geo.click_id = clicks.idclicks  
                    LEFT JOIN conversions ON conversions.click_id = clicks.idclicks                    ";
        }


        $sql .= "
                INNER JOIN rep 
                ON rep.lft > :left AND rep.rgt < :right
                where clicks.rep_idrep = rep.idrep   
               ";


        if ($this->assign->has("blacklist") && $this->assign->get("blacklist") == 1 && Session::userType() == \App\Privilege::ROLE_GOD) {
            $sql .= " AND clicks.click_type = 2";
        } else {
            $sql .= " AND clicks.click_type != 2";
        }


        if ($d_to && $d_from) {
            $sql .= " AND clicks.first_timestamp >= :d_from AND clicks.first_timestamp <=  :d_to ";
        }
        $sql .= " ORDER BY clicks.idclicks DESC ";


        /*if ( ! $rowCount) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }*/


        $db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(":left", $rep["lft"]);
        $stmt->bindParam(":right", $rep["rgt"]);


        if ($d_from && $d_to) {
            $stmt->bindParam(":d_from", $d_from);
            $stmt->bindParam(":d_to", $d_to);
        }
        $stmt->execute();


        return $stmt;


    }


    public function queryAffiliate($d_from = false, $d_to = false, $repID, $items_per_page, $offset, $rowCount = false)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

		$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE);

        $per = Permissions::loadFromSession();


        if ($per->can("view_fraud_data")) {
            $sql = "SELECT  clicks.idclicks, clicks.first_timestamp, offer.offer_name, conversions.timestamp, conversions.paid, click_vars.url,  click_geo.ip,  clicks.offer_idoffer FROM clicks
                    LEFT JOIN click_vars ON click_vars.click_id = clicks.idclicks
                    LEFT JOIN click_geo ON click_geo.click_id = clicks.idclicks
                     LEFT JOIN conversions ON conversions.click_id = clicks.idclicks  
                    LEFT JOIN offer ON offer.idoffer = clicks.offer_idoffer
                    WHERE clicks.rep_idrep = :repID ";
        } else {
            $sql = "SELECT  clicks.first_timestamp, offer.offer_name, conversions.timestamp,  conversions.paid, click_vars.url,  click_geo.ip, clicks.offer_idoffer  FROM clicks
                    LEFT JOIN click_vars ON click_vars.click_id = clicks.idclicks
                    LEFT JOIN click_geo ON click_geo.click_id = clicks.idclicks
                     LEFT JOIN conversions ON conversions.click_id = clicks.idclicks
                     LEFT JOIN offer ON offer.idoffer = clicks.offer_idoffer
                    WHERE clicks.rep_idrep = :repID ";
        }


        if ($d_to && $d_from) {
            $sql .= " AND clicks.first_timestamp >= :d_from AND clicks.first_timestamp <  :d_to  ";
        }


        if ($this->assign->has("blacklist") && $this->assign->get("blacklist") == 1 && Session::userType() == \App\Privilege::ROLE_GOD) {
            $sql .= " AND clicks.click_type = 2";
        } else {
            $sql .= " AND clicks.click_type != 2 ";
        }

        $sql .= " ORDER BY clicks.idclicks DESC ";

        if ( ! $rowCount) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $prep = $db->prepare($sql);

        $prep->bindParam(":repID", $repID);

        if ($d_from != false && $d_to != false) {

            $prep->bindParam(":d_from", $d_from);
            $prep->bindParam(":d_to", $d_to);
        }

        $prep->execute();


        return $prep;

    }


    public function fetchReport($d_from = false, $d_to = false, $repID, $items_per_page, $offset)
    {
        if ($this->userType == \App\Privilege::ROLE_AFFILIATE) {
            $this->report = $this->queryAffiliate($d_from, $d_to, $repID, $items_per_page, $offset)
                                 ->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->report = $this->queryEmployee($d_from, $d_to, $repID, $items_per_page, $offset)
                                 ->fetchAll(PDO::FETCH_ASSOC);
        }

    }


    function printHeaders()
    {
        if ($this->userType == \App\Privilege::ROLE_AFFILIATE || $this->userType == \App\Privilege::ROLE_UNKNOWN) {
            echo " <th class=\"value_span9\">Offer ID</th>";
        } else {
            echo "<th class=\"value_span9\">Aff ID</th>";
            echo " <th class=\"value_span9\">Aff First Name</th>
                                <th class=\"value_span9\">Offer ID</th>";
        }

    }


    function printR()
    {


        foreach ($this->report as $row => $val) {
            echo "<tr>";

            foreach ($val as $key => $val2) {
                echo "<td class='value_span8'>" . $val2 . "</td>";

            }

            echo "</tr>";

        }

    }

    function process()
    {
        if ($this->userType != \App\Privilege::ROLE_AFFILIATE) {

            foreach ($this->report as $row => $key) {

                $this->report[$row]['idrep'] = "<a href='/user/{$key['idrep']}/clicks?d_from={$this->assign->get("d_from",Carbon::today()->format('Y-m-d'))}&d_to={$this->assign->get("d_to", Carbon::today()->format('Y-m-d'))}&dateSelect={$this->assign->get("dateSelect", 0)}'>{$key['idrep']}</a>";

            }


        }

        $per = Permissions::loadFromSession();


        if ($per->can("view_fraud_data")) {
            foreach ($this->report as $row => $val) {
                $geo = ClickGeo::findGeo($val['ip']);
                foreach ($geo as $key => $val2) {
                    $this->report[$row][$key] = $val2;
                }
            }

        } else {
            foreach ($this->report as $row => $val) {

                $geo = ClickGeo::findGeo($val['ip']);

                $this->report[$row]["isoCode"] = $geo["isoCode"];
                unset($this->report[$row]["ip"]);

            }
        }

        foreach ($this->report as $row => $val) {


            $subIDsArray = ClickVars::processUrlToSubIDArray($val['url']);

            $urlPosition = 0;
            foreach ($val as $keyName => $val2) {

                if ($keyName == "url") {
                    break;
                }
                $urlPosition++;

            }


            $temp = array_splice($val, 0, $urlPosition);

            $arrayEnd = array_splice($val, 1, (count($val)));


            foreach ($subIDsArray as $row2) {
                $temp[] = $row2;
            }

            $temp = array_merge($temp, $arrayEnd);


            $this->report[$row] = $temp;


        }


    }

}