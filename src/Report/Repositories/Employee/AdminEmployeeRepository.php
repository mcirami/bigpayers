<?php

namespace LeadMax\TrackYourStats\Report\Repositories\Employee;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;
use Termwind\Components\Raw;

class AdminEmployeeRepository extends Repository
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


    private function queryGetRequestedUserType($userType)
    {
        $db = $this->getDB();
        $sql = "SELECT
					rep.idrep, rep.user_name, rep.lft, rep.rgt
				FROM rep
				
				INNER JOIN privileges p on rep.idrep = p.rep_idrep AND  " . $this->returnQueryBasedOnUserType($userType);

        $sql .= " WHERE rep.lft > :left AND rep.rgt < :right";


        $prep = $db->prepare($sql);

        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);

        $prep->execute();

        return $prep;
    }

    private function returnQueryBasedOnUserType($userType)
    {
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


    private function getReferralRevenue($dateFrom, $dateTo)
    {
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

    private function getBonusesRevenue($dateFrom, $dateTo)
    {
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


    private function getConversions($dateFrom, $dateTo)
    {
        
        $adminUserID = Session::userID();
        $managers = DB::table('rep')->where('referrer_repid', '=', $adminUserID)->get()->pluck('idrep')->toArray();

        $result = DB::table('clicks')
        ->whereBetween('clicks.first_timestamp', [$dateFrom,$dateTo])
        ->where('clicks.click_type', '!=', 2)
        ->leftJoin('rep', function($query) use($managers) {
            $query->on('rep.idrep', '=', 'clicks.rep_idrep')->whereIn('referrer_repid', $managers);
        })
        ->join('privileges', 'rep.idrep', '=', 'privileges.rep_idrep')
        ->leftJoin('pending_conversions as pc', function($query) {
            $query->on('clicks.idclicks', '=', 'pc.click_id')->where('pc.converted', '=' , 0);
        })
        ->leftJoin('conversions', 'clicks.idclicks', '=', 'conversions.click_id')
        ->leftJoin('deductions', 'deductions.conversion_id', '=', 'conversions.id')
        ->leftJoin('conversions as deducted', 'deducted.id', '=', 'deductions.conversion_id')
        ->leftJoin('free_sign_ups', 'free_sign_ups.click_id', '=', 'clicks.idclicks')
        ->select(
            'rep.idrep',
            'rep.user_name',
            'rep.lft',
			'rep.rgt',
            DB::raw('COUNT(conversions.id) as Conversions'),
            DB::raw('SUM(conversions.paid) as Revenue'),
            DB::raw('COUNT(free_sign_ups.id) as FreeSignUps'),
            DB::raw('SUM(deducted.paid) as Deductions')
            )
            ->groupBy('rep.idrep')
            ->orderBy('conversions', 'desc')
            ->get()
            ->map(fn ($row) => get_object_vars($row))
            ->toArray();

        return $result;
    }


    private function getClicks($dateFrom, $dateTo)
    {

        $adminUserID = Session::userID();
        $managers = DB::table('rep')->where('referrer_repid', '=', $adminUserID)->get()->pluck('idrep')->toArray();

        $result = DB::table('clicks')
        ->whereBetween('clicks.first_timestamp', [$dateFrom,$dateTo])
        ->where('clicks.click_type', '!=', 2)
        ->leftJoin('rep', function($query) use($managers) {
            $query->on('rep.idrep', '=', 'clicks.rep_idrep')->whereIn('referrer_repid', $managers);
        })
        ->join('privileges', 'rep.idrep', '=', 'privileges.rep_idrep')
        ->leftJoin('pending_conversions as pc', function($query) {
            $query->on('clicks.idclicks', '=', 'pc.click_id')->where('pc.converted', '=' , 0);
        })->select(
            'rep.idrep',
            'rep.user_name',
            'rep.lft',
			'rep.rgt',
            DB::raw('COUNT(clicks.idclicks) as Clicks'),
            DB::raw('SUM(case when clicks.click_type = 0 then 1 else 0 end) as UniqueClicks'),
            DB::raw('COUNT(pc.id) as PendingConversions'))
            ->groupBy('rep.idrep')
            ->orderBy('Clicks', 'desc')
            ->get()
            ->map(fn ($row) => get_object_vars($row))
            ->toArray();

        // array of arrays
        return $result;
    }

	private function getCodes($dateFrom, $dateTo)
	{
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
    public function query($dateFrom, $dateTo): \PDOStatement
    {
        // TODO: Implement query() method.
    }


}