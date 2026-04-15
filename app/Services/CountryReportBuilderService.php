<?php

namespace App\Services;

use App\ClickGeoCache;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class CountryReportBuilderService
{
	public function buildFromIpSubqueries(object $clicksSubquery, object $conversionsSubquery): array
	{
		$reportCollection = DB::query()
		                      ->fromSub($clicksSubquery, 'clicks')
		                      ->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
			                      $join->on('clicks.ip_address', '=', 'conversions.ip_address');
		                      })
		                      ->select(
			                      'clicks.ip_address',
			                      'clicks.country_code',
			                      DB::raw('SUM(clicks.clicks) as total_clicks'),
			                      DB::raw('SUM(clicks.unique_clicks) as unique_clicks'),
			                      DB::raw('SUM(COALESCE(conversions.conversions, 0)) as total_conversions'),
		                      )
		                      ->groupBy('clicks.ip_address', 'clicks.country_code')
		                      ->orderBy('total_conversions', 'DESC')
		                      ->get();

		$reports = [];

		foreach ($reportCollection as $item) {
			if (is_null($item->country_code)) {
				$geo = ClickGeoCache::query()
				                    ->where('ip_address', $item->ip_address)
				                    ->first();

				if ($geo) {
					$item->country_code = $geo->country_code;
				} else {
					$geo = ClickGeo::findGeo($item->ip_address);
					$item->country_code = $geo['isoCode'] ?? null;
				}
			}

			$countryCode = $item->country_code;

			if (!isset($reports[$countryCode])) {
				$reports[$countryCode] = [
					'country_code' => $countryCode,
					'total_clicks' => 0,
					'unique_clicks' => 0,
					'total_conversions' => 0
				];
			}

			$reports[$countryCode]['total_clicks'] += $item->total_clicks;
			$reports[$countryCode]['unique_clicks'] += $item->unique_clicks;
			$reports[$countryCode]['total_conversions'] += $item->total_conversions;
		}

		return [
			'reportCollection' => $reportCollection,
			'reports' => collect($reports)->sortByDesc('total_conversions'),
		];
	}
}
