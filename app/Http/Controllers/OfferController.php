<?php

namespace App\Http\Controllers;


use App\Offer;
use App\OfferURL;
use App\Privilege;
use App\User;
use App\UserOffer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as InputRequest;
use Illuminate\Support\Facades\Session;
use LeadMax\TrackYourStats\Offer\Campaigns;
use LeadMax\TrackYourStats\Offer\URLs;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\Table\Paginate;

class OfferController extends Controller
{

	public function requestOffer($id)
	{
		$result = \LeadMax\TrackYourStats\Offer\RepHasOffer::requestOffer($id, \LeadMax\TrackYourStats\System\Session::userID());
		return response()->json($result);
	}

	public function dupe($id)
	{
		if (\LeadMax\TrackYourStats\Offer\Offer::duplicateOffer($id)) {
			$message = 'Success!';
		} else {
			$message = 'Oh noes!';
		}

		return back()->with(compact('message'));
	}

	public function delete($id)
	{
		\LeadMax\TrackYourStats\Offer\Offer::deleteOffer($id);

		return back();
	}

	public function showManage()
	{
		$data = array();

		$this->validate(request(), [
			'showInactive' => 'numeric|min:0|max:1'
		]);

		$urls = \App\Company::instance()->first()->offerUrls()->where('status',1)->get();
		/* @var $urls Collection */
		if ($urls->isEmpty()) {
			$url = new OfferURL();
			$url->url = request()->getHttpHost();
			$urls->add($url);
		}
		$urls = $urls->pluck('url')->toArray();
		$data['urls'] = $urls;


		$status = request('showInactive', 0) == 1 ? 0 : 1;
		$offers = \LeadMax\TrackYourStats\System\Session::user()->offers()
		                                                        ->where('offer.status','=', $status)
		                                                        ->leftJoin('campaigns', 'offer.campaign_id', '=', 'campaigns.id')
		                                                        ->select('offer.*', 'campaigns.name as campaign_name');

		if (\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_AFFILIATE) {
			$offers = $offers->leftJoin('bonus_offers', 'bonus_offers.offer_id', '=', 'offer.idoffer')->get();
			$data['requestableOffers'] = Offer::where('is_public', \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_REQUESTABLE)
			                                  ->whereRaw('offer.idoffer NOT IN (SELECT offer_idoffer FROM rep_has_offer WHERE rep_has_offer.rep_idrep = ' . \LeadMax\TrackYourStats\System\Session::userID() . ')')->get();
		} else {
			$offers = $offers->get();
		}

		foreach ($offers as $offer) {
			$offer["offer_name"] = htmlspecialchars($offer["offer_name"]);
		}

		$data = array_merge(compact('offers'), $data);
		return view('offer.manage', $data)->with(['data' => $data]);
	}

	public function showCreate()
	{
		$offer = Session::get('offer') ?: new Offer();

		return view('offer.create')->with(['offer' => $offer]);
	}

	public function getAssignableUsers()
	{
		return User::withRole(InputRequest::get('user_type') === Privilege::ROLE_MANAGER ? Privilege::ROLE_MANAGER : Privilege::ROLE_AFFILIATE)
		           ->myUsers()->select(['rep.idrep as id', 'rep.user_name as name'])->get()->toJson();
	}

	public function getAssignedUsers($offerId)
	{
		$offer = Offer::where('idoffer', '=', $offerId)->first();

		return $offer->affiliates()->get()->toJson();
	}

	public function showEdit($id)
	{
		$offer = Offer::where('idoffer', '=', $id)->first();


		return vieW('offer.edit');
	}

	private function validateOfferRequest(Request $request)
	{
		$this->validate($request, [
			'offer_name' => 'required|min:3',
			'url' => 'required',
			'offer_type' => 'required',
			'payout' => 'required|numeric',
			'status' => 'required|numeric',
			'is_public' => 'required|numeric',
		]);
	}

	public function create(Request $request)
	{
		$this->validateOfferRequest($request);
		DB::beginTransaction();
		$offer = new Offer($request->all());
		if (!$request->has('campaign_id')) {
			$offer->campaign_id = Campaigns::getDefaultCampaignId();
		}
		$offer->offer_timestamp = Carbon::now('UTC')->format('Y-m-d H:i:s');
		$offer->created_by = \LeadMax\TrackYourStats\System\Session::user()->idrep;
		$offer->save();

		$users = User::all()->whereIn('idrep', $request->users);
		if ($users->first()->role != \App\Privilege::ROLE_AFFILIATE) {
			$users = User::withRole(\App\Privilege::ROLE_AFFILIATE)->whereIn('referrer_repid', $users->pluck('idrep'));
		}

		foreach ($users as $user) {
			$userOffer = new UserOffer();
			$userOffer->rep_idrep = $user->idrep;
			$userOffer->offer_idoffer = $offer->idoffer;
			$userOffer->payout = $offer->payout;
			$userOffer->save();
		}
		DB::commit();
	}


	public function showOfferURLs()
	{

		$offerURLs = new URLs(Company::loadFromSession());

		$urls = $offerURLs->getOfferUrls()->fetchAll(\PDO::FETCH_ASSOC);

		return view('offer.urls', compact('urls'));
	}

	public function massAssign(Request $request)
	{
		$this->validate($request, [
			'users' => 'required|array',
			'offers' => 'required|array'
		]);
		\LeadMax\TrackYourStats\Offer\RepHasOffer::massAssignUsers($request->post('users'), $request->post('offers'),
			request('role', 3));

		if (request()->has("updatePayouts")) {
			\LeadMax\TrackYourStats\Offer\RepHasOffer::massUpdateOfferPayouts($request->post('offers'));
		}

		return back()->with('message', 'Success!');
	}

	public function showMassAssign()
	{
		$users = User::myUsers()->withRole(request('role', 3))->get();

		$offers = \LeadMax\TrackYourStats\System\Session::user()->offers()->get();

		return view('offer.mass-assign', compact('users', 'offers'));
	}

}