<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Offer;
use App\Privilege;
use App\Services\Repositories\Offer\OfferClicksRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Assignments;
use LeadMax\TrackYourStats\User\Permissions;
use App\Http\Traits\ClickTraits;
use App\Services\ClickGeoCacheService;

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
	        $offer = Offer::findOrFail($id);

	        $dates = self::getDates();
		    ['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);

	    $start = Carbon::parse( $dates['startDate'], 'America/New_York' );
	    $end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

	    $repo          = new OfferClicksRepository( $id, Session::user(),
		    Session::permissions()->can( Permissions::VIEW_FRAUD_DATA ) );
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

    public function showOfferClicks($id)
    {
        $dates = self::getDates();
        $offer = Offer::findOrFail($id);

        // TODO: This should REALLY be refactored
        $myAssignments = array(
            'd_from' => Carbon::today()->format('Y-m-d'),
            'd_to' => Carbon::today()->format('Y-m-d'),
            'dateSelect' => 0,
            'rpp' => 10,
            'idoffer' => $id,
        );


        $assign = new Assignments($myAssignments);

        $assign->getAssignments();
        $report = new \LeadMax\TrackYourStats\Report\ID\Offer($assign);

        $report->fetchReport($dates['startDate'], $dates['endDate']);

        /*$paginate = new Paginate(request()->query('rpp', 10),
            $report->getCount($dates['startDate'], $dates['endDate']));*/


        return view('report.clicks.offer', compact('report', 'offer', 'dates'));
    }

    public function showUsersClicks($userId)
    {
        $dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$selectedRole = (int) request()->query('role', Privilege::ROLE_AFFILIATE);

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

	public function showManagersClicks($id) {

		$dates = self::getDates();
		$affClicks = [];
		$start = Carbon::parse($dates['startDate'], 'America/New_York');
		$end = Carbon::parse($dates['endDate'], 'America/New_York');

		$managers = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->get();
		foreach ($managers as $manager) {

			$data = DB::table('clicks')
			          ->join('rep', function($join) use ($manager){
						  $join->on('clicks.rep_idrep', '=', 'rep.idrep');
						  $join->where('rep.referrer_repid', '=', $manager->idrep);
					  })
			          ->leftJoin('conversions', 'conversions.click_id', 'clicks.idclicks' )
			          ->where('offer_idoffer', '=', $id)
			          ->whereBetween('first_timestamp', array($start, $end))
			          ->select([
						  \DB::raw('COUNT(clicks.rep_idrep) as clicks'),
				          \DB::raw('COUNT(conversions.click_id) as conversions')
			          ])
			          ->get();

			$object      = [
				'user_id' => $manager->idrep,
				'user_name' => $manager->user_name,
				'clicks' =>  $data[0]->clicks,
				'conversions' => $data[0]->conversions
			];
			$affClicks[] = (object) $object;
		}

		return $affClicks;

	}

	public function searchClicks(Request $request, $id) {

		$dates = self::getDates();
		['startDate' => $startDate, 'endDate' => $endDate, 'dateSelect' => $dateSelect] = $this->reportDateContext($dates);
		$searchType = request()->query('searchType');
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
			       ->select(
					   'clicks.idclicks',
				       'clicks.first_timestamp as timestamp',
				       'offer.offer_name',
				       'conversions.timestamp  as conversion_timestamp',
				       'conversions.paid as paid',
				       'click_vars.url',
				       'click_vars.sub1',
				       'click_vars.sub2',
				       'click_vars.sub3',
				       'click_vars.sub4',
				       'click_vars.sub5',
				       'clicks.ip_address as ip_address',
				       'clicks.offer_idoffer  as offer_id'
			       )
			       ->orderBy( 'conversions.paid', 'DESC' )->paginate( 100 );
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
			        ->select(
						'clicks.idclicks as id',
				        'clicks.first_timestamp as timestamp',
				        'clicks.rep_idrep as affiliate_id',
				        'conversions.timestamp as conversion_timestamp',
				        'conversions.paid as paid',
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
			        ->orderBy( 'conversions.paid', 'DESC' )->paginate( 100 );
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
		$geoCode = request()->query('country');
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
