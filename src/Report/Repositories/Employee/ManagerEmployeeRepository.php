<?php

namespace LeadMax\TrackYourStats\Report\Repositories\Employee;


use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Date;

class ManagerEmployeeRepository extends Repository
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


        return $report;
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
					rp.timestamp BETWEEN :dateFrom AND :dateTo AND rep.referrer_repid = :referrer_repid
				 
			 GROUP BY  rep.idrep
			 ";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(":referrer_repid", Session::userID());

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
					bonus.timestamp BETWEEN :unixFrom AND :unixTo AND rep.referrer_repid = :referrer_repid
				 
			 GROUP BY  rep.idrep
			 ";

        $stmt = $db->prepare($sql);


        $stmt->bindValue(":referrer_repid", Session::userID());

        $unixFrom = Date::convertTimestampToEpoch($dateFrom);
        $unixTo = Date::convertTimestampToEpoch($dateTo);

        $stmt->bindParam(":unixFrom", $unixFrom);
        $stmt->bindParam(":unixTo", $unixTo);

        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    private function getClicks($dateFrom, $dateTo)
    {
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
				
				LEFT JOIN pending_conversions pc ON rawClicks.idclicks = pc.click_id  AND pc.converted = 0
				
				WHERE
					rep.referrer_repid = :referrer_repid AND rawClicks.first_timestamp BETWEEN :dateFrom AND :dateTo  and rawClicks.click_type !=2
				 
			 GROUP BY  rep.idrep
			 ORDER BY Clicks DESC
		";


        $stmt = $db->prepare($sql);

        $stmt->bindValue(":referrer_repid", Session::userID());

        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);

        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }


    private function getConversions($dateFrom, $dateTo)
    {
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
				
				LEFT JOIN free_sign_ups u on rawClicks.idclicks = u.click_id
				
				LEFT JOIN deductions ON deductions.conversion_id = c.id
				
				LEFT JOIN conversions deducted ON deducted.id = deductions.conversion_id
				
				
				WHERE
					c.timestamp BETWEEN :dateFrom AND :dateTo AND rep.referrer_repid = :referrer_repid
				 
			 GROUP BY  rep.idrep
			 ORDER BY Conversions DESC ";

        $stmt = $db->prepare($sql);


        $stmt->bindValue(":referrer_repid", Session::userID());

        $stmt->bindParam(":dateFrom", $dateFrom);
        $stmt->bindParam(":dateTo", $dateTo);


        $stmt->execute();


        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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