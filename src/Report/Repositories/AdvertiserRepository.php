<?php


namespace LeadMax\TrackYourStats\Report\Repositories;


use App\Click;
use App\Conversion;
use Illuminate\Support\Facades\DB;

class AdvertiserRepository extends Repository
{

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return \PDOStatement
     */
    protected function query($dateFrom, $dateTo): \PDOStatement
    {
        $sql = "
        SELECT 
          campaigns.id,
          campaigns.name,
          offer.idoffer,
          offer.offer_name,
          COUNT(clicks.idclicks) AS Clicks,
          COUNT(clicks.idclicks) AS UniqueClicks,
          COUNT(conversions.id) AS Conversions,
          SUM(conversions.paid) AS Revenue 
        FROM campaigns
         
        INNER JOIN offer ON offer.campaign_id = campaigns.id 
        
        INNER JOIN clicks ON clicks.offer_idoffer = offer.idoffer AND clicks.click_type != 2
        
        LEFT JOIN conversions ON conversions.click_id = clicks.idclicks
        
        LEFT JOIN free_sign_ups u on clicks.idclicks = u.click_id

        ORDER BY Clicks DESC, UniqueClicks DESC, Conversions DESC, Revenue DESC, campaigns.id ASC
        ";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    private function clicks($dateFrom, $dateTo)
    {
        $sql = "
        	SELECT
                    campaigns.id,
                    campaigns.name,	
					count(c.idclicks) Clicks,
					SUM(CASE WHEN c.click_type = 0 THEN 1 ELSE 0 END) UniqueClicks,
                    count(pc.id) as PendingConversions
				FROM
					campaigns
					
				INNER JOIN offer o on campaigns.id = o.campaign_id
					
				LEFT JOIN clicks c ON c.offer_idoffer = o.idoffer
				
                LEFT JOIN pending_conversions pc
                    ON pc.click_id = c.idclicks  
					AND pc.converted = 0
                
                WHERE
					c.first_timestamp BETWEEN :dateFrom AND :dateTo  and c.click_type !=2
			 GROUP BY campaigns.id, o.campaign_id
        ";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindValue(':dateFrom', $dateFrom);
        $stmt->bindValue(':dateTo', $dateTo);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function conversions($dateFrom, $dateTo)
    {
        $sql = "
				SELECT
				    campaigns.id,
				    campaigns.name,
				    'advertiser' as type,
					count(f.id) FreeSignUps,
					count(conversions.id) Conversions,
					sum(conversions.paid) Revenue,
					sum(deducted.paid) Deductions
				FROM
					campaigns
					
                INNER JOIN offer AS o ON o.campaign_id = campaigns.id
					
				LEFT JOIN clicks rawClicks ON rawClicks.offer_idoffer = o.idoffer
				
				LEFT JOIN conversions ON conversions.click_id = rawClicks.idclicks
				
				LEFT JOIN free_sign_ups f ON f.click_id = rawClicks.idclicks
				
				
				LEFT JOIN deductions ON deductions.conversion_id = conversions.id
				
				LEFT JOIN conversions deducted ON deducted.id = deductions.conversion_id
				
				WHERE
					conversions.timestamp BETWEEN :dateFrom AND :dateTo 
				 
			 GROUP BY campaigns.id, o.campaign_id
				
		";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindValue(":dateFrom", $dateFrom);
        $stmt->bindValue(":dateTo", $dateTo);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return array
     */
    public function between($dateFrom, $dateTo): array
    {
        return $this->mergeReport($this->clicks($dateFrom, $dateTo), $this->conversions($dateFrom, $dateTo));
//        return $this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC);
    }

	public function getAdvConversionsByOffer( $id, $dateFrom, $dateTo) {

		$clicksSubquery = Click::query()
		                       ->whereBetween('first_timestamp', [$dateFrom, $dateTo])
		                       ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		                       ->join('offer', function ($join) use ($id) {
								   $join->on('offer.idoffer', '=', 'clicks.offer_idoffer')
			                       ->where('offer.campaign_id', '=', $id);
		                       })
		                       ->selectRaw('
							        clicks.offer_idoffer AS offer_id,
							        offer.offer_name,
							        COUNT(clicks.idclicks) AS clicks,
							        SUM(clicks.click_type = ?) AS unique_clicks', [Click::TYPE_UNIQUE]
		                       )
		                       ->groupBy('clicks.offer_idoffer', 'offer.offer_name');

		$conversionsSubquery = Conversion::query()
		                                 ->whereBetween('timestamp', [$dateFrom, $dateTo])
		                                 ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
										->join('offer', function ($join) use ($id) {
											$join->on('offer.idoffer', '=', 'clicks.offer_idoffer')
											     ->where('offer.campaign_id', '=', $id);
										})
										->selectRaw('
									        clicks.offer_idoffer AS offer_id,
									        COUNT(conversions.id) AS conversions,
									        COALESCE(SUM(conversions.paid), 0) AS total
									    ')
										->groupBy('clicks.offer_idoffer');

		return DB::query()->fromSub($clicksSubquery, "clicks")
					->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
						$join->on('clicks.offer_id', '=', 'conversions.offer_id');
					})
					->selectRaw('
				        clicks.offer_name,
				        clicks.clicks AS total_clicks,
				        clicks.unique_clicks AS unique_clicks,
				        COALESCE(conversions.conversions, 0) AS conversions,
				        COALESCE(conversions.total, 0) AS total
				    ')
					->orderByDesc('conversions')->paginate(100);

	}
}