<?php

namespace LeadMax\TrackYourStats\Report\Repositories\Employee;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\User\Permissions;
use Termwind\Components\Raw;

class GodEmployeeRepository extends Repository
{

    public $SHOW_AFF_TYPE = \App\Privilege::ROLE_AFFILIATE;


    public function between($dateFrom, $dateTo): array
    {
        $report = $this->mergeReport($this->getClicks($dateFrom, $dateTo), $this->getConversions($dateFrom, $dateTo));
        $report = $this->mergeReport($report, $this->getCodes($dateFrom, $dateTo));

        $report = $this->mergeReport($report, $this->getBonusesRevenue($dateFrom, $dateTo));


        $report = $this->mergeReport($report, $this->getReferralRevenue($dateFrom, $dateTo));

        $report = $this->setRequiredKeysIfNotSet($report, [
                'idrep' => '',
                'user_name' => '',
                'Clicks' => 0,
                'UniqueClicks' => 0,
                'FreeSignUps' => 0,
                'PendingConversions' => 0,
                'Conversions' => 0,
                'Revenue' => 0,
                'Deductions' => 0,
                'Codes' => 0,
                'EPC' => 0,
                'BonusRevenue' => 0,
                'ReferralRevenue' => 0,
                'TOTAL' => 0,
            ]
        );


        $report = $this->sortByRequestedUserType($report);


        return $report;
    }

    private function sortByRequestedUserType($report)
    {
        $users = $this->queryGetRequestedUserType($this->SHOW_AFF_TYPE)->fetchAll(\PDO::FETCH_ASSOC);

        switch ($this->SHOW_AFF_TYPE) {
            case \App\Privilege::ROLE_GOD:
            case \App\Privilege::ROLE_ADMIN:
            case \App\Privilege::ROLE_MANAGER:
                foreach ($users as &$user) {
                    $user = $this->defaultArrayReportKeys($user);
                    foreach ($report as $row) {
                        if ($row["lft"] > $user["lft"] && $row["rgt"] < $user["rgt"]) {
                            $user = $this->addArrayValuesToOtherArray($row, $user);
                        }
                    }
                }

                return $users;

            case \App\Privilege::ROLE_AFFILIATE:
                return $report;
        }

    }


    private function addArrayValuesToOtherArray($initial, $output)
    {
        $output["Clicks"] += $initial["Clicks"];
        $output["UniqueClicks"] += $initial["UniqueClicks"];
        $output['PendingConversions'] += $initial['PendingConversions'];
        $output["Conversions"] += $initial["Conversions"];
        $output["Revenue"] += $initial["Revenue"];
        $output["Deductions"] += $initial["Deductions"];
        $output["Codes"] += $initial["Codes"];
        $output["FreeSignUps"] += $initial["FreeSignUps"];
        $output["BonusRevenue"] += $initial["BonusRevenue"];
        $output["ReferralRevenue"] += $initial["ReferralRevenue"];

        return $output;
    }

    private function defaultArrayReportKeys($array)
    {
        $array["Clicks"] = 0;
        $array["UniqueClicks"] = 0;
        $array['PendingConversions'] = 0;
        $array["Conversions"] = 0;
        $array["Revenue"] = 0;
        $array["Deductions"] = 0;
        $array["Codes"] = 0;
        $array["FreeSignUps"] = 0;
        $array["BonusRevenue"] = 0;
        $array["ReferralRevenue"] = 0;

        return $array;
    }


    private function queryGetRequestedUserType($userType): false|\PDOStatement {
        $db = $this->getDB();
        $sql = "SELECT
					rep.idrep, rep.user_name, rep.lft, rep.rgt
				FROM rep
				
				INNER JOIN privileges p on rep.idrep = p.rep_idrep AND  " . $this->returnQueryBasedOnUserType($userType);

	    if(!Session::permissions()->can('view_all_users')) {
		    $sql .= " WHERE rep.lft > :left AND rep.rgt < :right";
	    }


        $prep = $db->prepare($sql);

	    if(!Session::permissions()->can('view_all_users')) {
		    $prep->bindParam( ":left", Session::userData()->lft );
		    $prep->bindParam( ":right", Session::userData()->rgt );
	    }

        $prep->execute();

        return $prep;
    }

    private function returnQueryBasedOnUserType($userType): string {
        switch ($userType) {
            case \App\Privilege::ROLE_GOD:
                return "p.is_god = 1";

            case \App\Privilege::ROLE_ADMIN:
                return "p.is_admin = 1";

            case \App\Privilege::ROLE_MANAGER:
                return "p.is_manager = 1";

            case \App\Privilege::ROLE_AFFILIATE:
                return "p.is_rep = 1";

            default :
                return "undefined";
        }
    }


    private function getReferralRevenue($dateFrom, $dateTo): array {
        $db = $this->getDB();
        $sql = "
				SELECT
					rep.idrep,
					rep.user_name,
					sum(rp.paid) ReferralRevenue,
					rep.lft,
					rep.rgt
				FROM
					rep
					
				INNER JOIN privileges p on rep.idrep = p.rep_idrep
				
				
				
				LEFT JOIN referrals_paid rp on rep.idrep = rp.referred_aff_id
				
				
				
				WHERE
					rp.timestamp BETWEEN :dateFrom AND :dateTo
				 
			 GROUP BY  rep.idrep
			 ";

        $stmt = $db->prepare($sql);


        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);
        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    private function getBonusesRevenue($dateFrom, $dateTo): array {
        $db = $this->getDB();
        $sql = "
				SELECT
					rep.idrep,
					rep.user_name,
					sum(bonus.payout) BonusRevenue,
					rep.lft,
					rep.rgt
				FROM
					rep
					
				INNER JOIN privileges p on rep.idrep = p.rep_idrep
				
				
				LEFT JOIN click_bonus bonus on rep.idrep = bonus.aff_id
				
				
				
				
				WHERE
					bonus.timestamp BETWEEN :unixFrom AND :unixTo
				 
			 GROUP BY  rep.idrep
			 ";

        $stmt = $db->prepare($sql);


//		$stmt->bindParam(":dateFrom", $dateFrom);
//		$stmt->bindParam(":dateTo", $dateTo);

        $unixFrom = Carbon::parse($dateFrom)->format("U");
        $unixTo = Carbon::parse($dateTo)->format("U");

        $stmt->bindParam(":unixFrom", $unixFrom);
        $stmt->bindParam(":unixTo", $unixTo);

        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }


    private function getConversions($dateFrom, $dateTo): array {
        $db = $this->getDB();
        $sql = "
				SELECT
					rep.idrep,
					rep.user_name,
					count(c.id) Conversions,
					sum(c.paid) Revenue,
					count(u.id) FreeSignUps,
					sum(deducted.paid) Deductions,
					rep.lft,
					rep.rgt
				FROM
					rep
					
				INNER JOIN privileges p on rep.idrep = p.rep_idrep
				
				LEFT JOIN clicks rawClicks ON rawClicks.rep_idrep = rep.idrep
				
				LEFT JOIN conversions c on rawClicks.idclicks = c.click_id
				
				
				LEFT JOIN deductions ON deductions.conversion_id = c.id
				
				LEFT JOIN conversions deducted ON deducted.id = deductions.conversion_id
				
				LEFT JOIN free_sign_ups u on rawClicks.idclicks = u.click_id
				
				
				
				WHERE
					c.timestamp BETWEEN :dateFrom AND :dateTo
				 
			 GROUP BY  rep.idrep
			 ORDER BY Conversions DESC ";

        $stmt = $db->prepare($sql);


        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);


        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $result;
    }

    private function getCodes($dateFrom, $dateTo): array {
        $db = $this->getDB();
        $sql = "
				SELECT
					rep.idrep,
					rep.user_name,
					count(sms_orders.id) Codes,
					rep.lft,
					rep.rgt
				FROM
					rep

				INNER JOIN privileges p on rep.idrep = p.rep_idrep

				LEFT JOIN sms_orders on sms_orders.rep_id = rep.idrep

				WHERE
					sms_orders.status = 'received'
					AND sms_orders.created_at BETWEEN :dateFrom AND :dateTo

				GROUP BY rep.idrep
				ORDER BY Codes DESC
			";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as &$row) {
            $row['Codes'] = (int) $row['Codes'];
        }

        return $result;
    }


    private function getClicks($dateFrom, $dateTo): array {
        

        $db = $this->getDB();
        $sql = "
				SELECT
					rep.idrep,
					rep.user_name,
					count(rawClicks.idclicks) Clicks,
					SUM(CASE WHEN rawClicks.click_type = 0 THEN 1 ELSE 0 END) UniqueClicks,
					count(pc.id) PendingConversions,
					rep.lft,
					rep.rgt
				FROM
					rep

				INNER JOIN privileges p on rep.idrep = p.rep_idrep
				
				LEFT JOIN clicks rawClicks ON rawClicks.rep_idrep = rep.idrep
				
				LEFT JOIN pending_conversions pc on rawClicks.idclicks = pc.click_id AND pc.converted = 0
				
				WHERE
					rawClicks.first_timestamp BETWEEN :dateFrom AND :dateTo  and rawClicks.click_type !=2

			    GROUP BY  rep.idrep
			    ORDER BY Clicks DESC
		";

        $stmt = $db->prepare($sql);


        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function query($dateFrom, $dateTo): \PDOStatement
    {
        // TODO: Implement query() method.
    }


}
