<?php


namespace App\Services\Repositories\Offer;


use App\Click;
use App\Conversion;
use App\Privilege;
use App\Services\CountryReportBuilderService;
use App\Services\Repositories\Repository;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Session;

/**
 * Reporting repository for Offers clicks, organized by affiliates.
 * @package App\Services\Repositories\Offer
 */
class OfferAffiliateClicksRepository implements Repository
{
    /**
     * @var
     */
    private $offerId;

    /**
     * @var User
     */
    private $user;

    /**
     * OfferAffiliateClicksRepository constructor.
     * @param $offerId
     * @param User $user
     */
    public function __construct($offerId, User $user)
    {
        $this->offerId = $offerId;
        $this->user = $user;
    }

    /**
     * Return the Eloquent query builder.
     * @param Carbon $start
     * @param Carbon $end
     * @return Builder
     */
    public function query(Carbon $start, Carbon $end): Builder
    {
	    if( Session::userType() == Privilege::ROLE_ADMIN) {
		    $report = Session::permissions()->can('view_all_users') ?
			    $this->getOfferConversionsForGod($start, $end)
			    :
			    $this->getOfferConversionsForAdmin($start, $end);

	    } else if( Session::userType() == Privilege::ROLE_MANAGER) {
            $report = $this->getOfferConversionsForManager($start, $end);
	    } else if( Session::userType() == Privilege::ROLE_AFFILIATE) {
            $report = $this->getOfferConversionsForAgent($start, $end);
        } else {
            $report = $this->getOfferConversionsForGod($start, $end);
        }
                                    
        return $report;
    }

    /**
     * Fetch results with the given dates.
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function between(Carbon $start, Carbon $end)
    {
        return $this->query($start, $end)->get();
    }

    public function getOfferConversionsForAgent($start, $end): Builder {
        return $this->buildRoleScopedOfferConversionsQuery(
            $start,
            $end,
            function (JoinClause $join) {
                $join->on('idrep', '=', 'clicks.rep_idrep');
            },
            function (Builder $query) {
                $query->where('rep_idrep', '=', $this->user->idrep);
            }
        );
    }

    public function getOfferConversionsForManager($start, $end) {
        return $this->buildRoleScopedOfferConversionsQuery(
            $start,
            $end,
            function (JoinClause $join) {
                $join->on('idrep', '=', 'clicks.rep_idrep')
                    ->where('rep.referrer_repid', $this->user->idrep);
            }
        );
    }

    public function getOfferConversionsForAdmin($start, $end): Builder {

        $managers = User::where('referrer_repid', $this->user->idrep)->pluck('idrep')->toArray();

        return $this->buildRoleScopedOfferConversionsQuery(
            $start,
            $end,
            function (JoinClause $join) use ($managers) {
                $join->on('idrep', '=', 'clicks.rep_idrep')
                    ->whereIn('rep.referrer_repid', $managers);
            }
        );
    }

    private function buildRoleScopedOfferConversionsQuery(
        $start,
        $end,
        callable $repJoinCallback,
        ?callable $additionalClickFilters = null
    ): Builder {
        $query = DB::table('clicks')
            ->where('offer_idoffer', '=', $this->offerId)
            ->whereBetween('first_timestamp', [$start, $end]);

        if ($additionalClickFilters !== null) {
            $additionalClickFilters($query);
        }

        return $query
            ->join('rep', $repJoinCallback)
            ->rightJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
            ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
            ->select(
                'rep.idrep as user_id',
                'rep.user_name',
                'offer.idoffer as offer_id',
                'offer.offer_name',
                DB::raw('COUNT(clicks.idclicks) as clicks'),
                DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks'),
                DB::raw('COUNT(conversions.click_id) as conversions')
            )
            ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
            ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForGod($start, $end) {

	    $clicksSubquery = Click::where('offer_idoffer', '=', $this->offerId)
	        ->whereBetween('first_timestamp',[$start, $end])
	        ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
	        ->join('rep', 'idrep', '=', 'clicks.rep_idrep')
	        ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		    ->groupBy('rep.user_name', 'rep.idrep', 'offer.idoffer')
		    ->select(
	            'rep.idrep as user_id',
	            'rep.user_name',
	            'offer.idoffer as offer_id',
	            'offer.offer_name',
	            DB::raw('COUNT(clicks.idclicks) as clicks'),
	            DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
		    );

	    $conversionsSubquery = Conversion::whereBetween('timestamp', [$start, $end])
		    ->join('clicks', 'conversions.click_id', '=', 'clicks.idclicks') // Join instead of whereIn
		    ->where('clicks.offer_idoffer', '=', $this->offerId)
		    ->groupBy('clicks.offer_idoffer', 'clicks.rep_idrep')
		    ->select('clicks.offer_idoffer as conv_offer_id',
			    'clicks.rep_idrep as user_id',
			    DB::raw('COUNT(conversions.id) as conversions')
		    );

	    return DB::query()
	                ->fromSub($clicksSubquery, 'clicks')
	                ->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
		                $join->on('clicks.offer_id', '=', 'conversions.conv_offer_id') // Match offer_id
		                     ->on('clicks.user_id', '=', 'conversions.user_id'); // Match user_id
	                }
	                )
	                ->select(
						'clicks.user_id',
						'clicks.user_name',
		                'clicks.clicks as clicks',
		                'clicks.unique_clicks as unique_clicks',
		                DB::raw('COALESCE(conversions.conversions, 0) as conversions')
	                )
	                ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsByCountry(CountryReportBuilderService $service, $start, $end) {

        $clicksSubquery = Click::whereBetween('first_timestamp', [$start, $end])
            ->where('offer_idoffer', '=', $this->offerId)
            ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
            ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
            ->select(
                'idclicks',
                'ip_address',
                'country_code',
                'click_type',
                DB::raw('COUNT(idclicks) as clicks'),
                DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
            )
            ->groupBy('ip_address', 'clicks.country_code');

        $conversionsSubquery = Conversion::whereBetween('timestamp', [$start, $end])
            ->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
            ->where('clicks.offer_idoffer', '=', $this->offerId)
            ->select(
                'clicks.ip_address',
                'clicks.country_code',
                DB::raw('COUNT(conversions.id) as conversions')
            )
            ->groupBy('clicks.ip_address', 'clicks.country_code');

        $countryReports = $service->buildFromIpSubqueries($clicksSubquery, $conversionsSubquery);

        return $countryReports['reports'];
    }
}
