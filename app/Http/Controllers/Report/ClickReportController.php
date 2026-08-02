<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Offer;
use App\Privilege;
use App\Http\Traits\ClickTraits;
use App\Services\ClickGeoCacheService;
use App\Services\Repositories\Offer\OfferClicksRepository;
use App\Support\CurrentUserSession;
use App\Support\UserDomain\Permissions;
use App\Support\OfferDomain\Payouts;
use App\Support\RequestContext;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClickReportController extends ReportController
{

	use ClickTraits;

    /**
     * Shows an offers clicks, and affiliates with those clicks.
     * Shows only affiliates assigned to the current logged in user
     *
     * @param $id
     *
     * @return Factory|View
     */
	    public function offerClicks($id)
	    {
            $currentUserContext = CurrentUserSession::snapshot();
	        $offer = Offer::findOrFail($id);

	        $dates = self::getDates();
		    ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

	    $start = Carbon::parse( $dates['startDate'], 'America/New_York' );
	    $end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

	    $repo = new OfferClicksRepository(
            $id,
            $currentUserContext->user(),
		    $currentUserContext->can(Permissions::VIEW_FRAUD_DATA)
        );
	    $reportCollection      = $repo->between( $start, $end );
		$report                = $reportCollection->items();

        return view('report.clicks.offer', 
		compact(
			'offer', 
			'report', 
			'reportCollection', 
			'id', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
    } 

    public function showUsersClicks($userId)
    {
        $dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$selectedRole = (int) RequestContext::query('role', Privilege::ROLE_AFFILIATE);

        $user = User::myUsers()->findOrFail($userId);

		$reportCollection = Click::query()
			->userClicksReportByRole($userId, $dates['startDate'], $dates['endDate'], $selectedRole)
			->paginate(100);

		$report = $this->formatResults($reportCollection);

        return view('report.clicks.affiliate', 
		compact(
			'report', 
			'user', 
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect',
			'selectedRole'
		));
    }

	public function searchClicks(Request $request, $id) {

		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$searchType = RequestContext::query('searchType');
        $selectedRole = (int) RequestContext::query('role', CurrentUserSession::type());
        $resolvedPaid = Payouts::sqlForRole($selectedRole, 'offer', 'rep_has_offer');
		$user = null;
		$offer = null;

		if ($searchType == "user") {

			$user = User::myUsers()->findOrFail( $id );
			$reportCollection = Click::where([
				[ 'rep_idrep', '=', $id ],
				[function ( $query ) use ( $request ) {
						if ( $s = $request->searchValue ) {
							$query->orWhere( 'idclicks', 'LIKE', '%' . $s . '%' );
						}
					}
				]])->whereBetween( 'clicks.first_timestamp', [ $dates['startDate'], $dates['endDate'] ] )
			       ->leftJoin( 'click_vars', 'click_vars.click_id', '=', 'clicks.idclicks' )
			       ->leftJoin( 'conversions', 'conversions.click_id', '=', 'clicks.idclicks' )
			       ->leftJoin( 'offer', 'offer.idoffer', '=', 'clicks.offer_idoffer' )
                   ->leftJoin('rep_has_offer', function ($join) {
                       $join->on('rep_has_offer.offer_idoffer', '=', 'clicks.offer_idoffer')
                           ->on('rep_has_offer.rep_idrep', '=', 'clicks.rep_idrep');
                   })
			       ->select(
					   'clicks.idclicks',
				       'clicks.first_timestamp as timestamp',
				       'offer.offer_name',
				       'conversions.timestamp  as conversion_timestamp',
				       DB::raw($resolvedPaid . ' as paid'),
				       'click_vars.url',
				       'click_vars.sub1',
				       'click_vars.sub2',
				       'click_vars.sub3',
				       'click_vars.sub4',
				       'click_vars.sub5',
				       'clicks.ip_address as ip_address',
				       'clicks.offer_idoffer  as offer_id'
			       )
			       ->orderBy( 'paid', 'DESC' )->paginate( 100 );
		} else {

			$offer = Offer::findOrFail($id);
			$reportCollection = Click::where([
				[ 'offer_idoffer', '=', $id ],
				[function ( $query ) use ( $request ) {
					if ( $s = $request->searchValue ) {
						$query->orWhere( 'idclicks', 'LIKE', '%' . $s . '%' )->orWhere('click_vars.encoded', 'LIKE', '%' . $s . '%' );
					}
				}]])->whereBetween( 'clicks.first_timestamp', [ $dates['startDate'], $dates['endDate'] ] )
			        ->leftJoin( 'click_vars', 'click_vars.click_id', '=', 'clicks.idclicks' )
			        ->leftJoin( 'conversions', 'conversions.click_id', '=', 'clicks.idclicks' )
			        ->leftJoin( 'offer', 'offer.idoffer', '=', 'clicks.offer_idoffer' )
                    ->leftJoin('rep_has_offer', function ($join) {
                        $join->on('rep_has_offer.offer_idoffer', '=', 'clicks.offer_idoffer')
                            ->on('rep_has_offer.rep_idrep', '=', 'clicks.rep_idrep');
                    })
			        ->select(
						'clicks.idclicks as id',
				        'clicks.first_timestamp as timestamp',
				        'clicks.rep_idrep as affiliate_id',
				        'conversions.timestamp as conversion_timestamp',
				        DB::raw($resolvedPaid . ' as paid'),
				        'click_vars.sub1',
				        'click_vars.sub2',
				        'click_vars.sub3',
				        'click_vars.sub4',
				        'click_vars.sub5',
						'click_vars.encoded',
				        'clicks.ip_address as ip_address',
				        'clicks.offer_idoffer as offer_id',
				        'offer.offer_name',
			        )
			        ->orderBy( 'paid', 'DESC' )->paginate( 100 );
		}


		$report = $this->formatResults($reportCollection);

		if ($searchType == "user") {
			return view('report.clicks.affiliate', compact('user', 'report', 'reportCollection', 'startDate', 'endDate', 'dateSelect'));
		} else {
			return view('report.clicks.offer', compact('offer', 'report', 'reportCollection', 'startDate', 'endDate', 'dateSelect'));
		}
	}

	public function clicksInCountry(ClickGeoCacheService $geoCache) {
		$dates = self::getDates();
		$geoCode = RequestContext::query('country');
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

		$ips = Click::missingCountryCodeIps($dates['startDate'], $dates['endDate']);

		$geoCache->warm($ips);

		$report = Click::query()
		               ->countryClicksInGeo($dates['startDate'], $dates['endDate'], $geoCode)
		               ->paginate(100);

		return view('report.clicks.clicks-in-country',
			compact(
				'report',
				'geoCode',
				'startDate',
				'endDate',
				'dateSelect'
			));
	}


}
