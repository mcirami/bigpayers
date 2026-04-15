<?php

namespace App\Http\Controllers\Report;

use App\Conversion;
use App\User;
use App\Click;
use App\Offer;
use App\Privilege;
use App\Services\ClickGeoCacheService;
use App\Services\CountryReportBuilderService;
use App\Http\Traits\ClickTraits;
use Illuminate\Support\Facades\DB;

class ConversionReportController extends ReportController
{
    use ClickTraits;

    public function showUserConversions($userId) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$offerId = request()->query('offer');

        $user = User::myUsers()->findOrFail($userId);

		$reportCollection = Conversion::where('user_id', '=', $userId)
	                ->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
					->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
	                ->leftJoin('click_vars', 'click_vars.click_id', '=', 'conversions.click_id')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						'conversions.paid as paid',
						'click_vars.sub1',
						'click_vars.sub2',
		                'click_vars.sub3',
		                'click_vars.sub4',
		                'click_vars.sub5',
	                )
					->where('clicks.offer_idoffer', '=', $offerId)
	                ->orderBy('conversions.timestamp', 'DESC')->paginate(100);

		$report = $this->formatResults($reportCollection);

        return view('report.conversions.affiliate', compact(
			'report', 
			'user', 
			'reportCollection',
			'startDate', 
			'endDate', 
			'dateSelect',
			'offerId'
		));
	}

    public function showUserConversionsByOffer($userId) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$selectedRole = (int) request()->query('role', Privilege::ROLE_AFFILIATE);

        $user = User::findOrFail($userId);

		$affiliateScope = function ($query, string $column) use ($userId, $selectedRole) {
			if (in_array($selectedRole, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER], true)) {
				$query->whereIn($column, function ($subQuery) use ($userId) {
					$subQuery->from('rep as child')
						->select('child.idrep')
						->join('privileges as p', 'p.rep_idrep', '=', 'child.idrep')
						->where('p.is_rep', '=', 1)
						->whereRaw('child.lft > (SELECT lft FROM rep WHERE idrep = ?)', [$userId])
						->whereRaw('child.rgt < (SELECT rgt FROM rep WHERE idrep = ?)', [$userId]);
				});
			} else {
				$query->where($column, '=', $userId);
			}
		};

		$clicksSubquery = Click::query();
		$affiliateScope($clicksSubquery, 'rep_idrep');
		$clicksSubquery
			->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
			->select(
				'ip_address', 
				'first_timestamp', 
				'idclicks', 
				'offer_idoffer', 
				'click_type', 
				DB::raw('COUNT(idclicks) as clicks'),
				DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
				)
			->groupBy('offer_idoffer');

		$conversionsSubquery = Conversion::query();
		$affiliateScope($conversionsSubquery, 'user_id');
		$conversionsSubquery
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->select('offer.idoffer', DB::raw('COUNT(conversions.id) as conversions'))
			->groupBy('offer.idoffer');

		$report = DB::query()
			->fromSub($clicksSubquery, 'clicks')
			->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
				$join->on('clicks.offer_idoffer', '=', 'conversions.idoffer');
			})
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->select(
				'offer.idoffer',
				'offer.offer_name',
				'clicks.clicks as total_clicks',
				'clicks.unique_clicks as unique_clicks',
				DB::raw('COALESCE(conversions.conversions, 0) as conversions'),
			)
			->orderBy('conversions', 'DESC')
			->paginate(100);

			return view('report.conversions.affiliate-by-offer', 
			compact(
				'report', 
				'user', 
				'startDate', 
				'endDate', 
				'dateSelect'
			));
	}

    public function showUserOfferConversionsByCountry(
		User $user,
		Offer $offer,
		CountryReportBuilderService $countryReportBuilderService
	) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
	        $userId = $user->idrep;
		$offerId = $offer->idoffer;

		$clicksSubquery = Click::query()
			->countryClicksByIpInGeo($dates['startDate'], $dates['endDate'], $userId, $offerId);

		$conversionsSubquery = Conversion::query()
			->countryConversionsByIpInGeo($dates['startDate'], $dates['endDate'], $userId, $offerId);

		$countryReports = $countryReportBuilderService
			->buildFromIpSubqueries($clicksSubquery, $conversionsSubquery);
		$reportCollection = $countryReports['reportCollection'];
		$reports = $countryReports['reports'];

		return view('report.conversions.affiliate-by-country', 
		compact(
			'reportCollection',
			'reports',
			'user', 
			'startDate', 
			'endDate', 
			'dateSelect', 
			'offer',
		));
	}

	public function showConversionsByCountry(CountryReportBuilderService $countryReportBuilderService) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

		$clicksSubquery = Click::query()
		                       ->countryClicksByIpInGeo($dates['startDate'], $dates['endDate']);

		$conversionsSubquery = Conversion::query()
			->countryConversionsByIpInGeo($dates['startDate'], $dates['endDate']);

		$countryReports = $countryReportBuilderService
			->buildFromIpSubqueries($clicksSubquery, $conversionsSubquery);
		$reports = $countryReports['reports'];

		return view('report.conversions.geo',
			compact(
				'reports',
				'startDate',
				'endDate',
				'dateSelect',
			));
	}

	public function showGeoByOffer(ClickGeoCacheService $geoCache) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$geoCode = request()->query('country');

		$ipsMissingGeo = Click::missingCountryCodeIps($dates['startDate'], $dates['endDate']);
		$geoCache->warm($ipsMissingGeo);

		$clicksByOffer = Click::query()
		                      ->countryClicksByOfferInGeo($dates['startDate'], $dates['endDate'], $geoCode);

		$conversionsByOffer = Conversion::query()
		                                ->countryConversionsByOfferInGeo($dates['startDate'], $dates['endDate'], $geoCode);


		$report = DB::query()
		            ->fromSub($clicksByOffer, 'c')
		            ->leftJoinSub($conversionsByOffer, 'v', function ($join) {
			            $join->on('c.offer_id', '=', 'v.offer_id')
			                 ->on('c.country_code', '=', 'v.country_code');
		            })
		            ->selectRaw('
		        c.offer_name,
		        c.offer_id,
		        c.country_code,
		        c.total_clicks,
		        c.unique_clicks,
		        COALESCE(v.total_conversions, 0) AS total_conversions
		    ')
		            ->orderByDesc('total_conversions')
		            ->get();

		$totals = [
			'total_clicks' => (int) $report->sum('total_clicks'),
			'unique_clicks' => (int) $report->sum('unique_clicks'),
			'total_conversions' => (int) $report->sum('total_conversions'),
		];

		return view('report.conversions.offer-geo',
			compact(
				'geoCode',
				'report',
				'totals',
				'startDate',
				'endDate',
				'dateSelect',
			));

	}

	public function showManagerConversionsByOffer(User $user) {

		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$managerId = $user->idrep;

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		                       ->whereIn('rep_idrep', function ($query) use ($managerId) {
			                       $query->select('idrep')->from('rep')->where('referrer_repid', '=', $managerId);
		                       })
		                       ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		                       ->select(
			                       'offer_idoffer',
			                       DB::raw('COUNT(idclicks) as clicks'),
			                       DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
		                       )
		                       ->groupBy('offer_idoffer');

		$conversionsSubquery = Conversion::whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
		                                 ->whereIn('user_id', function ($query) use ($managerId) {
			                                 $query->select('idrep')->from('rep')
			                                       ->where('rep.referrer_repid', '=', $managerId);
		                                 })
		                                 ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
		                                 ->select(
			                                 'clicks.offer_idoffer',
			                                 DB::raw('COUNT(conversions.id) as conversions'))
		                                 ->groupBy('clicks.offer_idoffer');

		$report = DB::query()
		            ->fromSub($clicksSubquery, 'clicks')
		            ->joinSub($conversionsSubquery, 'conversions', function ($join) {
			            $join->on('clicks.offer_idoffer', '=', 'conversions.offer_idoffer');
		            })
		            ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		            ->select(
			            'offer.idoffer',
			            'offer.offer_name',
			            'clicks.clicks as total_clicks',
			            'clicks.unique_clicks as unique_clicks',
			            DB::raw('COALESCE(conversions.conversions, 0) as conversions')
		            )
		            ->orderBy('conversions', 'DESC')
		            ->paginate(100);

		return view('report.conversions.affiliate-by-offer',
			compact(
				'user',
				'report',
				'startDate',
				'endDate',
				'dateSelect'
			));
	}
}
