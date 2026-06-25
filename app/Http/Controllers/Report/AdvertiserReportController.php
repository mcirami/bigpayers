<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Conversion;
use Illuminate\Support\Facades\DB;

class AdvertiserReportController extends ReportController
{
    public function show()
    {
        $dates = self::getDates();
        $clicks = DB::table('campaigns')
            ->join('offer', 'offer.campaign_id', '=', 'campaigns.id')
            ->join('clicks', 'clicks.offer_idoffer', '=', 'offer.idoffer')
            ->leftJoin('pending_conversions', function ($join) {
                $join->on('pending_conversions.click_id', '=', 'clicks.idclicks')
                    ->where('pending_conversions.converted', '=', 0);
            })
            ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
            ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
            ->groupBy('campaigns.id', 'campaigns.name')
            ->get([
                'campaigns.id',
                'campaigns.name',
                DB::raw('COUNT(clicks.idclicks) as Clicks'),
                DB::raw('SUM(CASE WHEN clicks.click_type = ' . Click::TYPE_UNIQUE . ' THEN 1 ELSE 0 END) as UniqueClicks'),
                DB::raw('COUNT(pending_conversions.id) as PendingConversions'),
            ]);

        $conversions = DB::table('campaigns')
            ->join('offer', 'offer.campaign_id', '=', 'campaigns.id')
            ->join('clicks', 'clicks.offer_idoffer', '=', 'offer.idoffer')
            ->join('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
            ->leftJoin('free_sign_ups', 'free_sign_ups.click_id', '=', 'clicks.idclicks')
            ->leftJoin('deductions', 'deductions.conversion_id', '=', 'conversions.id')
            ->leftJoin('conversions as deducted', 'deducted.id', '=', 'deductions.conversion_id')
            ->whereBetween('conversions.timestamp', [$dates['startDate'], $dates['endDate']])
            ->groupBy('campaigns.id', 'campaigns.name')
            ->get([
                'campaigns.id',
                'campaigns.name',
                DB::raw('COUNT(free_sign_ups.id) as FreeSignUps'),
                DB::raw('COUNT(conversions.id) as Conversions'),
                DB::raw('COALESCE(SUM(offer.payout), 0) as Revenue'),
                DB::raw('SUM(deducted.paid) as Deductions'),
            ]);

        $report = $this->mergeCampaignMetrics($clicks, $conversions);
        $report[] = $this->campaignTotals($report);

        return view('report.advertiser', compact('report', 'dates'));
    }

    public function showConversionsByOffer($id)
    {
        $dates = self::getDates();
        ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
        $clicksSubquery = Click::query()
            ->whereBetween('first_timestamp', [$startDate, $endDate])
            ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
            ->join('offer', function ($join) use ($id) {
                $join->on('offer.idoffer', '=', 'clicks.offer_idoffer')
                    ->where('offer.campaign_id', '=', $id);
            })
            ->selectRaw(
                'clicks.offer_idoffer AS offer_id,
                offer.offer_name,
                COUNT(clicks.idclicks) AS clicks,
                SUM(clicks.click_type = ?) AS unique_clicks',
                [Click::TYPE_UNIQUE]
            )
            ->groupBy('clicks.offer_idoffer', 'offer.offer_name');

        $conversionsSubquery = Conversion::query()
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
            ->join('offer', function ($join) use ($id) {
                $join->on('offer.idoffer', '=', 'clicks.offer_idoffer')
                    ->where('offer.campaign_id', '=', $id);
            })
            ->selectRaw(
                'clicks.offer_idoffer AS offer_id,
                COUNT(conversions.id) AS conversions,
                COALESCE(SUM(offer.payout), 0) AS total'
            )
            ->groupBy('clicks.offer_idoffer');

        $affiliateReport = DB::query()
            ->fromSub($clicksSubquery, 'clicks')
            ->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
                $join->on('clicks.offer_id', '=', 'conversions.offer_id');
            })
            ->selectRaw(
                'clicks.offer_name,
                clicks.clicks AS total_clicks,
                clicks.unique_clicks AS unique_clicks,
                COALESCE(conversions.conversions, 0) AS conversions,
                COALESCE(conversions.total, 0) AS total'
            )
            ->orderByDesc('conversions')
            ->paginate(100);

        return view('report.advertiser-offer-conversions', compact(
            'startDate',
            'endDate',
            'dateSelect',
            'affiliateReport'
        ));
    }

    private function mergeCampaignMetrics($clicks, $conversions): array
    {
        $rows = [];

        foreach ($clicks as $row) {
            $rows[$row->id] = [
                'id' => $row->id,
                'name' => $row->name,
                'Clicks' => (int) $row->Clicks,
                'UniqueClicks' => (int) $row->UniqueClicks,
                'PendingConversions' => (int) $row->PendingConversions,
                'FreeSignUps' => 0,
                'Conversions' => 0,
                'Revenue' => 0.0,
                'Deductions' => 0.0,
            ];
        }

        foreach ($conversions as $row) {
            $rows[$row->id] ??= [
                'id' => $row->id,
                'name' => $row->name,
                'Clicks' => 0,
                'UniqueClicks' => 0,
                'PendingConversions' => 0,
                'FreeSignUps' => 0,
                'Conversions' => 0,
                'Revenue' => 0.0,
                'Deductions' => 0.0,
            ];
            $rows[$row->id]['FreeSignUps'] = (int) $row->FreeSignUps;
            $rows[$row->id]['Conversions'] = (int) $row->Conversions;
            $rows[$row->id]['Revenue'] = (float) $row->Revenue;
            $rows[$row->id]['Deductions'] = (float) ($row->Deductions ?? 0);
        }

        return array_values(array_map(function (array $row) {
            $row['EPC'] = $row['UniqueClicks'] > 0
                ? round($row['Revenue'] / $row['UniqueClicks'], 2)
                : 0;
            $row['TOTAL'] = $row['Revenue'];

            return $row;
        }, $rows));
    }

    private function campaignTotals(array $report): array
    {
        $totals = [
            'id' => 'TOTAL',
            'name' => '',
            'Clicks' => 0,
            'UniqueClicks' => 0,
            'PendingConversions' => 0,
            'FreeSignUps' => 0,
            'Conversions' => 0,
            'Revenue' => 0.0,
            'Deductions' => 0.0,
            'TOTAL' => 0.0,
        ];

        foreach ($report as $row) {
            foreach (['Clicks', 'UniqueClicks', 'PendingConversions', 'FreeSignUps', 'Conversions', 'Revenue', 'TOTAL'] as $key) {
                $totals[$key] += $row[$key];
            }
        }

        $totals['EPC'] = $totals['UniqueClicks'] > 0
            ? round($totals['Revenue'] / $totals['UniqueClicks'], 2)
            : 0;

        return $totals;
    }

}
