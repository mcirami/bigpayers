<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Offer;
use App\Services\CountryReportBuilderService;
use App\User;
use App\Conversion;
use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\ClickGeo;
use App\Support\OfferDomain\Payouts;
use App\Support\RequestContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ClickTraits;

class SubReportController extends ReportController
{

	use ClickTraits;

    public function show()
    {
        $dates = self::getDates();
        $subColumn = $this->subReportColumn(RequestContext::query('sub', 1));
        $userId = CurrentUserSession::snapshot()->id;

        $clickRows = DB::table('clicks')
            ->join('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
            ->where('clicks.rep_idrep', $userId)
            ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
            ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
            ->groupBy("click_vars.{$subColumn}")
            ->select([
                "click_vars.{$subColumn} as sub",
                DB::raw('COUNT(clicks.idclicks) as clicks'),
                DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks'),
            ])
            ->get();

        $revenueRows = DB::table('conversions')
            ->join('clicks', function ($join) use ($userId) {
                $join->on('clicks.idclicks', '=', 'conversions.click_id')
                    ->where('clicks.rep_idrep', '=', $userId)
                    ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED);
            })
            ->join('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
            ->whereBetween('conversions.timestamp', [$dates['startDate'], $dates['endDate']])
            ->groupBy("click_vars.{$subColumn}")
            ->select([
                "click_vars.{$subColumn} as sub",
                DB::raw('SUM(conversions.paid) as revenue'),
                DB::raw('COUNT(conversions.paid) as conversions'),
            ])
            ->get();

        $report = $this->mergeSubReportRows($clickRows, $revenueRows);

        return view('report.sub', compact('report', 'dates'));
    }

    private function subReportColumn($subNumber): string
    {
        $subNumber = (int) $subNumber;

        return in_array($subNumber, [1, 2, 3], true) ? "sub{$subNumber}" : 'sub1';
    }

    private function mergeSubReportRows($clickRows, $revenueRows): array
    {
        $report = [];

        foreach ($clickRows as $row) {
            $sub = $this->normalizeSubReportKey($row->sub);

            $report[$sub] = [
                'sub' => $sub,
                'clicks' => (int) $row->clicks,
                'unique' => (int) $row->unique_clicks,
                'conversions' => 0,
                'revenue' => 0,
            ];
        }

        foreach ($revenueRows as $row) {
            $sub = $this->normalizeSubReportKey($row->sub);

            if (! isset($report[$sub])) {
                $report[$sub] = [
                    'sub' => $sub,
                    'clicks' => 0,
                    'unique' => 0,
                    'conversions' => 0,
                    'revenue' => 0,
                ];
            }

            $report[$sub]['conversions'] = (int) $row->conversions;
            $report[$sub]['revenue'] = (float) $row->revenue;
        }

        $report[] = [
            'sub' => 'TOTAL',
            'clicks' => array_sum(array_column($report, 'clicks')),
            'unique' => array_sum(array_column($report, 'unique')),
            'conversions' => array_sum(array_column($report, 'conversions')),
            'revenue' => '',
        ];

        return $report;
    }

    private function normalizeSubReportKey($sub): string
    {
        return $sub === null || $sub === '' ? '(empty)' : (string) $sub;
    }

	public function showSubConversions(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application {

		$subID = $request->get('subid');
		$dates = self::getDates();
        $currentUserContext = CurrentUserSession::snapshot();
        $resolvedPaid = $currentUserContext->type === Privilege::ROLE_AFFILIATE
            ? 'conversions.paid'
            : Payouts::sqlForRole($currentUserContext->type, 'offer', 'rep_has_offer');

		$subReport = DB::table('click_vars')
		  ->where('sub1', '=', $subID)
		  ->orWhere('sub2', '=', $subID)
		  ->orWhere('sub3', '=', $subID)
		  ->leftJoin('clicks', 'clicks.idclicks', '=', 'click_vars.click_id')
		  ->leftJoin('offer', 'clicks.offer_idoffer', '=', 'offer.idoffer')
          ->leftJoin('rep_has_offer', function ($join) {
              $join->on('rep_has_offer.offer_idoffer', '=', 'clicks.offer_idoffer')
                  ->on('rep_has_offer.rep_idrep', '=', 'clicks.rep_idrep');
          })
		  ->join('conversions', function($query) use($dates) {
			  $query->on('conversions.click_id', '=', 'clicks.idclicks')->whereBetween('conversions.timestamp', [$dates['startDate'], $dates['endDate']]);
		  })
		  ->select(DB::raw($resolvedPaid . ' as paid'), 'conversions.timestamp', 'offer.offer_name')->orderBy('offer.offer_name')->get();

		return view ('report.single-sub', compact('subReport', 'subID'));
	}

	public function showUserConversionsBySubId($userId, $offer) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$offerData = Offer::findOrFail($offer);
		$user = User::findOrFail($userId);

		$report = Click::where('rep_idrep', '=', $userId)
			->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
			->where('clicks.offer_idoffer', '=', $offer)
			->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
			->select(
				('click_vars.sub1'),
				DB::raw('COUNT(conversions.id) as conversions'),
				DB::raw('COUNT(clicks.idclicks) as clicks'),
				DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
			)
			->groupBy('click_vars.sub1')
			->orderBy('conversions', 'DESC')
			->paginate(100);

			return view ('report.conversions.affiliate-by-sub', 
			compact(
				'report',
				 'startDate', 
				 'endDate', 
				 'dateSelect',
				 'offerData',
				 'user'
				));
	}

	public function showSubIdClicksByOffer(User $user, Offer $offer) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$subId = RequestContext::query('subId');
        $currentUserContext = CurrentUserSession::snapshot();
        $resolvedPaid = $currentUserContext->type === Privilege::ROLE_AFFILIATE
            ? 'conversions.paid'
            : Payouts::sqlForRole($currentUserContext->type, 'offer', 'rep_has_offer');

	    $reportCollection = Click::where('rep_idrep', '=', $user->idrep)
					->where('clicks.offer_idoffer', '=', $offer->idoffer)
	                ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', function($query) {
						$query->on('click_vars.click_id', '=', 'clicks.idclicks')
						->whereRaw('clicks.first_timestamp >= NOW() - INTERVAL 2 YEAR');
					})
					->where('click_vars.sub1', '=', $subId)
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
                    ->leftJoin('rep_has_offer', function ($join) {
                        $join->on('rep_has_offer.offer_idoffer', '=', 'clicks.offer_idoffer')
                            ->on('rep_has_offer.rep_idrep', '=', 'clicks.rep_idrep');
                    })
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp as timestamp',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						DB::raw($resolvedPaid . ' as paid'),
						'click_vars.url',
						'click_vars.sub1 as subId',
						'clicks.referer',
						'clicks.ip_address as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);

		$reportCollection->appends(['subId' => $subId]);
		$report = $this->formatResults($reportCollection);

		
        return view('report.clicks.subid', 
		compact(
			'report', 
			'user',
			'subId',
			'offer',
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
	}

	public function showSubIdConversionsInCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$country = RequestContext::query('country');
		$userId = $user->idrep;
		$offerId = $offer->idoffer;

		$ipAddresses = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) {
			$query->whereNull('country_code')
			->orWhere('country_code', '');
		})
		->pluck('ip_address')
		->toArray();

		$matchingIPs = array_filter($ipAddresses, function ($ip) use ($country) {
			$geo = ClickGeo::findGeo($ip);
			return $geo['isoCode'] === $country;
		});

		$clicksSubquery = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) use($matchingIPs, $country) {
			$query->where('country_code', '=', $country)
			->orWhere(function ($subQuery) use ($matchingIPs) {
				$subQuery->whereIn('ip_address', $matchingIPs);
			});
		})
		->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
		->where('click_vars.sub1', '!=', '')
		->select(
			'click_vars.sub1 as subId',
			DB::raw('COUNT(clicks.idclicks) as clicks'), 
			DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks'))
		->groupBy('subId');

		$conversionsSubquery = Conversion::where('user_id', '=', $userId)
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->where('click_vars.sub1', '!=', '')
			->where('clicks.offer_idoffer', '=', $offerId)
			->where(function ($query) use($matchingIPs, $country) {
				$query->where('clicks.country_code', '=', $country)
				->orWhere(function ($subQuery) use ($matchingIPs) {
					$subQuery->whereIn('clicks.ip_address', $matchingIPs);
				});
			})
			->select(
				'click_vars.sub1 as subId', 
				DB::raw('COUNT(conversions.id) as total_conversions'))
			->groupBy('click_vars.sub1')
			->orderBy('total_conversions');

			$reportCollection = DB::query()
			->fromSub($clicksSubquery, 'clicks')
			->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
				$join->on('clicks.subId', '=', 'conversions.subId');
			})
			->select(
				'clicks.subId',
				DB::raw('SUM(clicks.clicks) as total_clicks'),
				DB::raw('SUM(clicks.unique_clicks) as unique_clicks'),
				DB::raw('SUM(COALESCE(conversions.total_conversions, 0)) as total_conversions'),
			)
			->groupBy('clicks.subId')
			->orderBy('total_conversions', 'DESC')->paginate(100);

		return view('report.conversions.affiliate-sub-in-country', 
		compact(
			'reportCollection',
			'user',
			'startDate', 
			'endDate', 
			'dateSelect',
			'offer',
			'country'
		));
	}

	public function showSubIdClicksByOfferInCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$subId = RequestContext::query('subid');
		$country = RequestContext::query('country');
		$userId = $user->idrep;
		$offerId = $offer->idoffer;
        $currentUserContext = CurrentUserSession::snapshot();
        $resolvedPaid = $currentUserContext->type === Privilege::ROLE_AFFILIATE
            ? 'conversions.paid'
            : Payouts::sqlForRole($currentUserContext->type, 'offer', 'rep_has_offer');

		$ipAddresses = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) {
			$query->whereNull('country_code')
			->orWhere('country_code', '');
		})
		->pluck('ip_address')
		->toArray();

		$matchingIPs = array_filter($ipAddresses, function ($ip) use ($country) {
			$geo = ClickGeo::findGeo($ip);
			return $geo['isoCode'] === $country;
		});

	    $reportCollection = Click::where('rep_idrep', '=', $user->idrep)
					->where('clicks.offer_idoffer', '=', $offer->idoffer)
	                ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
					->where('click_vars.sub1', '=', $subId)
					->where(function ($query) use($matchingIPs, $country) {
						$query->where('country_code', '=', $country)
						->orWhere(function ($subQuery) use ($matchingIPs) {
							$subQuery->whereIn('ip_address', $matchingIPs);
						});
					})
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
                    ->leftJoin('rep_has_offer', function ($join) {
                        $join->on('rep_has_offer.offer_idoffer', '=', 'clicks.offer_idoffer')
                            ->on('rep_has_offer.rep_idrep', '=', 'clicks.rep_idrep');
                    })
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp as timestamp',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						DB::raw($resolvedPaid . ' as paid'),
						'click_vars.url',
						'click_vars.sub1 as subId',
						'clicks.referer',
						'clicks.ip_address  as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);
	
		$reportCollection->appends(['country' => $country, 'subid' => $subId]);
		$report = $this->formatResults($reportCollection);

		//dd($report);
		return view('report.clicks.subid-in-country', 
		compact(
			'report', 
			'user',
			'subId',
			'offer',
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect',
			'country'
		));
	}

	public function subIdOfferConverisonsByCountry(User $user, Offer $offer, CountryReportBuilderService $countryReportBuilderService) {
		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$subId = RequestContext::query('subid');
        $userId = $user->idrep;
		$offerId = $offer->idoffer;

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		->where('click_vars.sub1', '=', $subId)
		->select(
			'click_vars.sub1',
			'idclicks',
			'ip_address',
			'country_code',
			'click_type',
			DB::raw('COUNT(idclicks) as clicks'),
			DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks'))
		->groupBy('ip_address');

		$conversionsSubquery = Conversion::where('user_id', '=', $userId)
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->where('clicks.offer_idoffer', '=', $offerId)
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->where('click_vars.sub1', '=', $subId)
			->select(
				'clicks.ip_address', 
				'clicks.country_code',
				DB::raw('COUNT(conversions.id) as conversions'))
			
			->groupBy('clicks.ip_address', 'clicks.country_code');

		$countryReports = $countryReportBuilderService
			->buildFromIpSubqueries($clicksSubquery, $conversionsSubquery);
		$reportCollection = $countryReports['reportCollection'];
		$reports = $countryReports['reports'];

		return view('report.conversions.affiliate-subid-by-country', 
		compact(
			'reportCollection',
			'reports',
			'subId',
			'user', 
			'startDate', 
			'endDate', 
			'dateSelect', 
			'offer',
		));
	}
}
