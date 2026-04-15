<?php

namespace App\Services\Repositories\Offer;

use App\Click;
use App\Services\Repositories\Repository;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\App\_IH_Click_C;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;
use App\Http\Traits\ClickTraits;

/**
 * Reporting Repository for an Offers clicks.
 * @package App\Services\Repositories\Offer
 */
class OfferClicksRepository implements Repository
{
	use ClickTraits;

    /**
     * @var
     */
    public $offerId;

    /**
     * @var bool
     */
    public $showFraudData;

    /**
     * @var User
     */
    public $user;

    /**
     * OfferClicksRepository constructor.
     * @param $offerId
     * @param User $user
     * @param bool $showFraudData
     */
    public function __construct($offerId, User $user, $showFraudData = false)
    {
        $this->offerId = $offerId;
        $this->user = $user;
        $this->showFraudData = $showFraudData;
    }

	/**
	 * Return the Eloquent query builder.
	 *
	 * @param Carbon $start
	 * @param Carbon $end
	 *
	 * @return _IH_Click_C|array|LengthAwarePaginator
	 */
    public function query(Carbon $start, Carbon $end): _IH_Click_C|array|LengthAwarePaginator {
        $select = [];
        if ($this->showFraudData) {
            $select[] = 'clicks.idclicks as id';
        }
        $select = array_merge($select, [
            'clicks.first_timestamp as timestamp',
            'conversions.timestamp as conversion_timestamp',
            'conversions.paid as paid',
            'click_vars.url as query_string',
	        'click_vars.url',
	        'click_vars.sub1',
	        'click_vars.sub2',
	        'click_vars.sub3',
	        'click_vars.encoded',
            'clicks.referer',
            'clicks.rep_idrep as affiliate_id',
            'clicks.offer_idoffer as offer_id',
	        'clicks.ip_address as ip_address',
	        'clicks.country_code as isoCode'
        ]);
	    if(Session::permissions()->can('view_all_users')) {
		    return Click::leftJoin('click_vars', 'click_vars.click_id', 'clicks.idclicks')
		                ->leftJoin('conversions', 'conversions.click_id', 'clicks.idclicks')
		                ->join('rep', 'rep.idrep', 'clicks.rep_idrep')
		                ->where('offer_idoffer', $this->offerId)
		                ->whereBetween('clicks.first_timestamp', [$start, $end])
		                ->select($select)
		                ->orderBy('paid', 'DESC')
		                ->paginate(100);
	    } else {
		    return Click::leftJoin('click_vars', 'click_vars.click_id', 'clicks.idclicks')
		                ->leftJoin('conversions', 'conversions.click_id', 'clicks.idclicks')
		                ->join('rep', 'rep.idrep', 'clicks.rep_idrep')
		                ->where('offer_idoffer', $this->offerId)
		                ->where('rep.lft', '>', $this->user->lft)
		                ->where('rep.rgt', '<', $this->user->rgt)
		                ->whereBetween('clicks.first_timestamp', [$start, $end])
		                ->select($select)
		                ->orderBy('paid', 'DESC')
		                ->paginate(100);
	    }
    }

    /**
     * Fetch results with the given dates, with additional result formatting.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return object
     */
    public function between(Carbon $start, Carbon $end): object {
        return $this->formatResults($this->query($start, $end));
    }

}