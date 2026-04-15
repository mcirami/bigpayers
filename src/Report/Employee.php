<?php
/**
 * Created by PhpStorm.
 * rep: dean
 * Date: 7/24/2017
 * Time: 4:14 PM
 */


// Report for Employers, This report will show clicks underneath the logged in user
// Reports->Affiliate Report (nav bar)

namespace LeadMax\TrackYourStats\Report;


use LeadMax\TrackYourStats\Report\Repositories\ReferralRepository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\Table\ReportBase;

class Employee extends ReportBase
{

    public $referrals = array();

    private $repType = -1;

    public $assign;

    public $referralRepo;

    public function __construct($repType, $assignments = false)
    {
        if ($assignments !== false) {
            $this->assign = $assignments;
        }
        $this->repType = $repType;

        $this->referralRepo = new ReferralRepository();
    }


    public function resetReportArrayKeys()
    {
        $temp = [];
        foreach ($this->report as $item) {
            $temp[] = $item;
        }

        $this->report = $temp;
    }

    //needed to be different for affiliate report
    function printReport($lastRowStatic = false)
    {
        $this->resetReportArrayKeys();

        foreach ($this->report as $row => $key) {
            if ($lastRowStatic && $row == count($this->report) - 1) {
                echo "<tr class='static'>";
            } else {
                echo "<tr>";
            }

            echo "<td>" . $key["idrep"] . "</td>";
            echo "<td>" . $key["user_name"] . "</td>";
            echo "<td>" . $key["Clicks"] . "</td>";
            echo "<td>" . $key["UniqueClicks"] . "</td>";
            echo "<td>" . $key["free_sign_ups"] . "</td>";
            echo "<td>" . $key["Conversions"] . "</td>";
            echo "<td>" . $key["Revenue"] . "</td>";
            echo "<td>" . $key["EPC"] . "</td>";

            echo "<td>" . $key["BonusPayout"] . "</td>";
            echo "<td>" . $key["Referral_Revenue"] . "</td>";
            echo "<td>" . $key["TOTAL"] . "</td>";;
            echo "</tr>";
        }


    }


    function process($showRepType, $noClicks)
    {


        if ($this->repType != \App\Privilege::ROLE_MANAGER) {


            switch ($showRepType) {
                case 1: //admins

                    $admins = array();

                    foreach ($this->report as $row => $rootKey) {
                        if ($rootKey["is_admin"] == 1) {
                            array_push($admins, $rootKey);
                        }
                    }


                    foreach ($admins as $key => $val) {
                        foreach ($this->report as $key2 => $val2) {
                            //gets managers clicks, conversions, revenue, then removes rep
                            if ($val2["lft"] > $val["lft"] && $val2["rgt"] < $val["rgt"]) {
                                //add clicks
                                $admins[$key]["Clicks"]       += $val2["Clicks"];
                                $admins[$key]["UniqueClicks"] += $val2["UniqueClicks"];

                                //conversions
                                $admins[$key]["Conversions"] += $val2["Conversions"];
                                //revenue
                                $admins[$key]["Revenue"] += $val2["Revenue"];

                                $admins[$key]["Referral_Revenue"] += $val2["Referral_Revenue"];


                                $admins[$key]["BonusPayout"] += $val2["BonusPayout"];
                            }


                        }
                    }
                    $this->report = $admins;

                    break;

                case 2: //managers

                    $managers = array();

                    foreach ($this->report as $rootKey => $row) {

                        if ($row["is_manager"] == 1) {
                            array_push($managers, $this->report[$rootKey]);
                        }


                    }


                    foreach ($managers as $key => $val) {

                        foreach ($this->report as $key2 => $val2) {
                            //gets managers clicks, conversions, revenue, then removes rep
                            if ($val2["lft"] > $val["lft"] && $val2["rgt"] < $val["rgt"]) {

                                //add clicks
                                $managers[$key]["Clicks"]       += $val2["Clicks"];
                                $managers[$key]["UniqueClicks"] += $val2["UniqueClicks"];
                                //                                        $this->report[$row][2] = $key[left];

                                //conversions
                                $managers[$key]["Conversions"] += $val2["Conversions"];
                                //revenue
                                $managers[$key]["Revenue"] += $val2["Revenue"];


                                $managers[$key]["Referral_Revenue"] += $val2["Referral_Revenue"];

                                $managers[$key]["BonusPayout"] += $val2["BonusPayout"];


                            }
                        }

                    }


                    $this->report = $managers;

                    break;

                case 3:
                    //reps
                    foreach ($this->report as $row => $rootKey) {
                        if ($rootKey["is_rep"] != 1) {
                            unset($this->report[$row]);
                        }
                    }
                    break;

            }
        }


        switch ($showRepType) {
            case 1:
                $toShow = "is_admin";
                break;
            case 2 :
                $toShow = "is_manager";
                break;
            case 3:
                $toShow = "is_rep";
                break;
            default:
                $toShow = "is_rep";
                break;

        }

        //                $sql = "SELECT rep_has_offer.offer_idoffer, offer.offer_name FROM rep_has_offer JOIN offer ON offer.idoffer = rep_has_offer.offer_idoffer WHERE rep_has_offer.rep_idrep = 31";
        if ($noClicks == 0) {
            foreach ($this->report as $row => $key) {
                if ($key["Clicks"] == 0 && $key["UniqueClicks"] == 0 && $key["Conversions"] == 0 && $key["Referral_Revenue"] == 0) {
                    unset($this->report[$row]);
                }
            }
        } else {
            if ($this->repType != \App\Privilege::ROLE_MANAGER) {
                foreach ($this->report as $row => $key) {
                    if ($key[$toShow] != 1) {
                        unset($this->report[$row]);
                    }
                }
            }

        }


        foreach ($this->report as $key => $row) {
            if ( ! isset($row['Revenue'])) {
                $this->report [$key]["Revenue"] = 0;
            }

            if ( ! isset($row["BonusPayout"])) {
                $this->report[$key]["BonusPayout"] = 0;
            }

            $this->report[$key]["TOTAL"] = $row["Revenue"] + $row["Referral_Revenue"] + $this->report[$key]["BonusPayout"];


        }


        $this->totalAll(["idrep", "user_name", "EPC"]);


        foreach ($this->report as $key => &$val) {
            $val["user_name"] = ReportBase::createUserTooltip($val["user_name"], $val["idrep"]);
        }


        $this->EPC();

        $this->dollarSignThese(["Revenue", "Referral_Revenue", "EPC", "BonusPayout", "TOTAL"]);

        foreach ($this->report as $row => $key) {
            if ($key["idrep"] !== "TOTAL") {
                $this->report [$row]['Clicks'] = "<a href='/user/{$key['idrep']}/clicks?d_from={$this->assign->get("d_from")}&d_to={$this->assign->get("d_to")}&dateSelect={$this->assign->get("dateSelect")}'>{$key['Clicks']}</a>";
            }

        }

    }

    function fetchReport($items_per_page, $offset, $d_from = false, $d_to = false, $showRepType = false)
    {

        switch ($this->repType) {
            case \App\Privilege::ROLE_GOD:
                $this->report = $this->reportGod($items_per_page, $offset, $d_from, $d_to)->fetchAll(\PDO::FETCH_ASSOC);
                break;

            case \App\Privilege::ROLE_ADMIN:
                $this->report = $this->reportGod($items_per_page, $offset, $d_from, $d_to)->fetchAll(\PDO::FETCH_ASSOC);
                break;

            case \App\Privilege::ROLE_MANAGER:
                $this->report = $this->reportManager($items_per_page, $offset, $d_from, $d_to)
                                     ->fetchAll(\PDO::FETCH_ASSOC);
                break;
        }
    }


    function rowCount($d_from = false, $d_to = false, $showRepType = false)
    {
        switch ($this->repType) {
            case \App\Privilege::ROLE_GOD:
                $this->report = $this->reportGod(false, false, $d_from, $d_to)->rowCount();
                break;

            case \App\Privilege::ROLE_ADMIN:
                $this->report = $this->reportGod(false, false, $d_from, $d_to)->rowCount();
                break;

            case \App\Privilege::ROLE_MANAGER:
                $this->report = $this->reportManager(false, false, $d_from, $d_to)->rowCount();
                break;
        }
    }


    function reportGod($items_per_page = false, $offset = false, $d_from = false, $d_to = false)
    {


        $sql = "SELECT
                rep.idrep,
                rep.user_name,
                
                (SELECT COUNT(*) FROM clicks WHERE clicks.rep_idrep = rep.idrep AND clicks.click_type != 2 ";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from AND clicks.first_timestamp <  :d_to  ";
        }


        $sql .= "
               ) as Clicks,
               
                   (SELECT COUNT(*) FROM clicks WHERE clicks.rep_idrep = rep.idrep AND clicks.click_type = 0 ";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from4 AND clicks.first_timestamp <=  :d_to4  ";
        }


        $sql .= " ) as UniqueClicks,
               
  (SELECT count(*)  FROM clicks INNER JOIN conversions ON conversions.click_id = clicks.idclicks  
               
                   ";
        if ($d_from && $d_to) {
            $sql .= "  AND conversions.timestamp >= :d_from2 AND conversions.timestamp <= :d_to2   ";
        }

        $sql .= " WHERE clicks.rep_idrep = rep.idrep";


        $sql .= ") AS Conversions,
            
				(SELECT COUNT(*) FROM clicks INNER JOIN free_sign_ups ON free_sign_ups.click_id = clicks.idclicks
				AND free_sign_ups.timestamp BETWEEN :freeDateFrom AND :freeDateTo
				WHERE clicks.rep_idrep = rep.idrep) as free_sign_ups,
    
  (SELECT sum(conversions.paid)  FROM clicks INNER JOIN conversions ON conversions.click_id = clicks.idclicks    
";

        if ($d_from && $d_to) {
            $sql .= "  and conversions.timestamp >= :d_from3 AND conversions.timestamp <= :d_to3  ";
        }

        $sql .= " WHERE clicks.rep_idrep  = rep.idrep
          ) AS Revenue
          ,
          (SELECT sum(paid) FROM referrals_paid WHERE referred_aff_id = rep.idrep AND timestamp >= :d_from5 AND timestamp <= :d_to5)
          as Referral_Revenue,
          
          privileges.is_admin,
                        privileges.is_manager,
                        privileges.is_rep,
                        lft,
                        rgt,
                        (SELECT sum(payout) FROM click_bonus WHERE aff_id = rep.idrep  AND click_bonus.timestamp >= :bonus_start_date AND click_bonus.timestamp <= :bonus_end_date ) as BonusPayout
        
         FROM
            rep ";


        $sql .= "
    
            INNER JOIN privileges
            ON privileges.rep_idrep = rep.idrep
            
         
          
            
          
            
           
          
            WHERE rep.lft > :left AND rep.rgt < :right    
            
            GROUP BY rep.idrep, privileges.idprivileges
                
            ORDER BY Clicks DESC, Conversions DESC  ";


        if ($items_per_page && $offset) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }

        $db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $stmt = $db->prepare($sql);


        $userData = Session::userData();

        $stmt->bindParam(":left", $userData->lft);
        $stmt->bindParam(":right", $userData->rgt);


        if ($d_from != false && $d_to != false) {

            $bonus_from = Date::convertTimestampToEpoch($d_from);
            $bonus_to   = Date::convertTimestampToEpoch($d_to);

            $stmt->bindParam(":d_from", $d_from);
            $stmt->bindParam(":d_from2", $d_from);
            $stmt->bindParam(":d_from3", $d_from);
            $stmt->bindParam(":d_from4", $d_from);
            $stmt->bindParam(":d_from5", $d_from);
            $stmt->bindParam(":freeDateFrom", $d_from);

            $stmt->bindParam(":d_to", $d_to);
            $stmt->bindParam(":d_to2", $d_to);
            $stmt->bindParam(":d_to3", $d_to);
            $stmt->bindParam(":d_to4", $d_to);
            $stmt->bindParam(":d_to5", $d_to);
            $stmt->bindParam(":freeDateTo", $d_to);

            $stmt->bindParam(":bonus_start_date", $bonus_from);
            $stmt->bindParam(":bonus_end_date", $bonus_to);


        }
        $stmt->execute();

        return $stmt;


    }

    function reportManager($items_per_page, $offset, $d_from = false, $d_to = false)
    {
        $sql = "SELECT
                rep.idrep,
                rep.user_name,
                
                (SELECT COUNT(*) FROM clicks WHERE clicks.rep_idrep = rep.idrep AND clicks.click_type != 2 ";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from AND clicks.first_timestamp <=  :d_to  ";
        }


        $sql .= "
               ) as Clicks,
                   (SELECT COUNT(*) FROM clicks  WHERE clicks.rep_idrep = rep.idrep AND clicks.click_type = 0 ";
        if ($d_from && $d_to) {
            $sql .= "  AND clicks.first_timestamp >= :d_from5 AND clicks.first_timestamp <=  :d_to5   ";
        }


        $sql .= "
               )as UniqueClicks,
               
                  (SELECT COUNT(*) FROM clicks  INNER JOIN conversions ON conversions.click_id = clicks.idclicks  ";
        if ($d_from && $d_to) {
            $sql .= " AND conversions.timestamp >= :d_from2 AND conversions.timestamp <=  :d_to2   ";
        }

        $sql .= " WHERE clicks.rep_idrep = rep.idrep ";


        $sql .= ") AS Conversions,
			
			
				(SELECT COUNT(*) FROM clicks INNER JOIN free_sign_ups ON free_sign_ups.click_id = clicks.idclicks
				AND free_sign_ups.timestamp BETWEEN :freeDateFrom AND :freeDateTo
				WHERE clicks.rep_idrep = rep.idrep) as free_sign_ups,
			
			
  (SELECT sum(conversions.paid)  FROM clicks INNER JOIN conversions ON conversions.click_id = clicks.idclicks 
          ";

        if ($d_from && $d_to) {
            $sql .= "  AND conversions.timestamp >= :d_from3 AND conversions.timestamp <=  :d_to3  ";
        }

        $sql .= "
            WHERE clicks.rep_idrep  = rep.idrep   
          ) AS Revenue
          ,
          (SELECT sum(paid) FROM referrals_paid WHERE referred_aff_id = rep.idrep AND timestamp >= :d_from4   AND :d_to4 <= timestamp) 
          as Referral_Revenue,
          
          
                        lft,
                        rgt,
                        (SELECT sum(payout) FROM click_bonus WHERE aff_id = rep.idrep  AND click_bonus.timestamp >= :bonus_start_date AND click_bonus.timestamp <= :bonus_end_date ) as BonusPayout
        
         FROM
            rep ";


        $sql .= "
    
            INNER JOIN privileges
            ON privileges.rep_idrep = rep.idrep
            
          
           
          
            WHERE rep.lft > :left AND rep.rgt < :right
            
            GROUP BY rep.idrep, privileges.rep_idrep
                
            ORDER BY Clicks DESC, Conversions DESC  ";


        if ($items_per_page && $offset) {
            $sql .= "LIMIT $items_per_page ";
            $sql .= "OFFSET {$offset}";
        }


        $db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $stmt = $db->prepare($sql);


        $userData = Session::userData();


        $stmt->bindParam(":left", $userData->lft);
        $stmt->bindParam(":right", $userData->rgt);


        if ($d_from != false && $d_to != false) {


            $bonus_from = Date::convertTimestampToEpoch($d_from);
            $bonus_to   = Date::convertTimestampToEpoch($d_to);


            $stmt->bindParam(":d_from", $d_from);
            $stmt->bindParam(":d_from2", $d_from);
            $stmt->bindParam(":d_from3", $d_from);
            $stmt->bindParam(":d_from4", $d_from);
            $stmt->bindParam(":d_from5", $d_from);
            $stmt->bindParam(":freeDateFrom", $d_from);

            $stmt->bindParam(":d_to", $d_to);
            $stmt->bindParam(":d_to2", $d_to);
            $stmt->bindParam(":d_to3", $d_to);
            $stmt->bindParam(":d_to4", $d_to);
            $stmt->bindParam(":d_to5", $d_to);
            $stmt->bindParam(":freeDateTo", $d_to);

            $stmt->bindParam(":bonus_start_date", $bonus_from);
            $stmt->bindParam(":bonus_end_date", $bonus_to);


        }
        $stmt->execute();

        return $stmt;
    }


}
	


