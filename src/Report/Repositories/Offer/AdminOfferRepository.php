<?php

namespace LeadMax\TrackYourStats\Report\Repositories\Offer;

use App\User;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Report\Repositories\Repository;
use LeadMax\TrackYourStats\System\Session;

class AdminOfferRepository extends Repository
{

    public function query($dateFrom, $dateTo): \PDOStatement
    {
        //todo lol

    }


    public function between($dateFrom, $dateTo): array
    {
        $clicks = $this->getClicks($dateFrom, $dateTo);

        $conversions = $this->getConversions($dateFrom, $dateTo);
        $report = $this->mergeReport($clicks, $conversions);

        $report = $this->setRequiredKeysIfNotSet($report, [
            'idoffer' => '',
            'offer_name' => '',
            'Clicks' => 0,
            'UniqueClicks' => 0,
            'FreeSignUps' => 0,
            'PendingConversions' => 0,
            'Conversions' => 0,
            'Revenue' => 0,
            'Deductions' => 0,
            'EPC' => 0,
        ]);


        return $report;
    }


    private function getClicks($dateFrom, $dateTo)
    {

        $managers = User::where('referrer_repid', Session::user()->idrep)->pluck('idrep')->toArray();

        $result = DB::table('offer')
            ->leftJoin('clicks as rawClicks', 'rawClicks.offer_idoffer', 'offer.idoffer')
            ->leftJoin('pending_conversions as pc', function ($jc) {
                /* @var $jc JoinClause */
                $jc->on('pc.click_id', 'rawClicks.idclicks')->where('pc.converted', 0);
            })
            ->join('rep', 'rep.idrep', 'rawClicks.rep_idrep')
            ->whereBetween('rawClicks.first_timestamp', [$dateFrom, $dateTo])
            ->where('rawClicks.click_type', '!=', 2)
            ->whereIn('rep.referrer_repid', $managers)
            ->select([
                'offer.idoffer',
                'offer.offer_name',
                DB::raw('COUNT(rawClicks.idclicks) as Clicks'),
                DB::raw('SUM(CASE WHEN rawClicks.click_type = 0 THEN 1 ELSE 0 END) as UniqueClicks'),
                DB::raw('COUNT(pc.id) as PendingConversions')
            ])
            ->groupBy('offer.idoffer', 'rawClicks.offer_idoffer')->get()->toArray();
        
        return json_decode(json_encode($result), true);
    }

    private function getConversions($dateFrom, $dateTo)
    {
        $managers = User::where('referrer_repid', Session::user()->idrep)->pluck('idrep')->toArray();
        $result = DB::table('offer')
            ->leftJoin('clicks as rawClicks', 'rawClicks.offer_idoffer', 'offer.idoffer')
            ->leftJoin('conversions', 'conversions.click_id', 'rawClicks.idclicks')
            ->leftJoin('free_sign_ups as f', 'f.click_id', 'rawClicks.idclicks')
            ->leftJoin('deductions', 'deductions.conversion_id', 'conversions.id')
            ->leftJoin('conversions as deducted', 'deducted.id', 'deductions.conversion_id')
            ->join('rep', 'rep.idrep', 'conversions.user_id')
            ->whereBetween('conversions.timestamp', [$dateFrom, $dateTo])
            ->whereIn('rep.referrer_repid', $managers)
            ->select([
                'offer.idoffer',
                'offer.offer_name',
                DB::raw('COUNT(f.id) as FreeSignUps'),
                DB::raw('COUNT(conversions.id) as Conversions'),
                DB::raw('SUM(conversions.paid) as Revenue'),
                DB::raw('SUM(deducted.paid) as Deductions')
            ])
            ->groupBy('offer.idoffer', 'rawClicks.offer_idoffer')->get()->toArray();
        
        return json_decode(json_encode($result), true);
    }


}