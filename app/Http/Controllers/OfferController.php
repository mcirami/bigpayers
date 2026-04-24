<?php

namespace App\Http\Controllers;


use App\Campaign;
use App\Offer;
use App\OfferURL;
use App\PredefinedOfferRule;
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
		$campaigns = Campaign::query()->orderBy('name')->get();

		return view('offer.create')->with([
			'mode' => 'create',
			'pageTitle' => 'Create Offer',
			'pageHeading' => 'Create a new offer',
			'pageCopy' => 'Launch a new offer, choose how it appears in the directory, and assign it to the right ' . strtolower(config('branding.affiliate.plural')) . ' from the same screen.',
			'formAction' => '/offer/create',
			'submitLabel' => 'Create offer',
			'offer' => $offer,
			'campaigns' => $campaigns,
		]);
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
		$offer = Offer::query()->where('idoffer', '=', $id)->firstOrFail();
		$campaigns = Campaign::query()->orderBy('name')->get();

		return view('offer.create')->with([
			'mode' => 'edit',
			'pageTitle' => 'Edit Offer',
			'pageHeading' => 'Edit offer',
			'pageCopy' => 'Update the main offer details, payout, visibility, and advertiser assignment without dropping back into the legacy editor.',
			'formAction' => "/offer/edit/{$offer->idoffer}",
			'submitLabel' => 'Save changes',
			'offer' => $offer,
			'campaigns' => $campaigns,
		]);
	}

	public function showView($id)
	{
		$offer = Offer::query()
			->with(['campaign', 'affiliates' => function ($query) {
				$query->orderBy('user_name');
			}])
			->where('idoffer', '=', $id)
			->firstOrFail();

		return view('offer.show', [
			'offer' => $offer,
			'assignedUsers' => $offer->affiliates,
		]);
	}

	public function showRules($id)
	{
		$offer = Offer::query()->where('idoffer', '=', $id)->firstOrFail();
		$rules = new \LeadMax\TrackYourStats\Offer\Rules($offer->idoffer);
		$offerView = new \LeadMax\TrackYourStats\Offer\View(\LeadMax\TrackYourStats\System\Session::userType());
		$activeCap = false;
		$capAmount = 0;
		$geoRules = [];
		$deviceRules = [];

		foreach ($rules->rules as $rule) {
			if (($rule['type'] ?? null) === 'device') {
				$activeCap = (bool) ($rule['cap_status'] ?? false);
				$capAmount = $rule['cap'] ?? 0;
			}

			$ruleId = (int) ($rule['idrule'] ?? 0);

			if (($rule['type'] ?? null) === 'geo' && $ruleId > 0) {
				if (!isset($geoRules[$ruleId])) {
					$geoRules[$ruleId] = [
						'name' => $rule['name'] ?? '',
						'redirectOffer' => (int) ($rule['redirect_offer'] ?? 0),
						'is_active' => (int) ($rule['is_active'] ?? 0),
						'deny' => (int) ($rule['deny'] ?? 0),
						'countries' => [],
					];
				}

				if (!empty($rule['country_code'])) {
					$geoRules[$ruleId]['countries'][$rule['country_code']] = [
						'country_code' => $rule['country_code'],
						'cap_status' => (int) ($rule['cap_status'] ?? 0),
						'cap' => (int) ($rule['cap'] ?? 0),
					];
				}
			}

			if (($rule['type'] ?? null) === 'device' && $ruleId > 0) {
				if (!isset($deviceRules[$ruleId])) {
					$deviceRules[$ruleId] = [
						'name' => $rule['name'] ?? '',
						'redirectOffer' => (int) ($rule['redirect_offer'] ?? 0),
						'is_active' => (int) ($rule['is_active'] ?? 0),
						'deny' => (int) ($rule['deny'] ?? 0),
						'capAmount' => (int) ($rule['cap'] ?? 0),
						'capStatus' => (int) ($rule['cap_status'] ?? 0),
						'devices' => [],
					];
				}

				if (!empty($rule['device_type']) && !in_array($rule['device_type'], $deviceRules[$ruleId]['devices'], true)) {
					$deviceRules[$ruleId]['devices'][] = $rule['device_type'];
				}
			}
		}

		foreach ($geoRules as $ruleId => $data) {
			$geoRules[$ruleId]['countries'] = array_values($data['countries']);
		}

		$redirectOfferIds = collect($geoRules)
			->pluck('redirectOffer')
			->merge(collect($deviceRules)->pluck('redirectOffer'))
			->filter(fn ($value) => (int) $value > 0)
			->map(fn ($value) => (int) $value)
			->unique()
			->values();

		$redirectOfferMap = $redirectOfferIds->isEmpty()
			? []
			: Offer::query()
				->whereIn('idoffer', $redirectOfferIds->all())
				->pluck('offer_name', 'idoffer')
				->toArray();

        $predefinedGeoRules = PredefinedOfferRule::query()
            ->where('type', '=', 'geo')
            ->orderBy('name')
            ->get()
            ->map(function (PredefinedOfferRule $rule) {
                return [
                    'id' => (int) $rule->id,
                    'name' => $rule->name,
                    'rule_name' => $rule->rule_name,
                    'redirectOffer' => (int) $rule->redirect_offer,
                    'deny' => (int) $rule->deny,
                    'is_active' => (int) $rule->is_active,
                    'items' => $rule->items,
                ];
            })
            ->values()
            ->all();

        $predefinedDeviceRules = PredefinedOfferRule::query()
            ->where('type', '=', 'device')
            ->orderBy('name')
            ->get()
            ->map(function (PredefinedOfferRule $rule) {
                return [
                    'id' => (int) $rule->id,
                    'name' => $rule->name,
                    'rule_name' => $rule->rule_name,
                    'redirectOffer' => (int) $rule->redirect_offer,
                    'deny' => (int) $rule->deny,
                    'is_active' => (int) $rule->is_active,
                    'capAmount' => (int) $rule->cap_amount,
                    'capStatus' => (int) $rule->cap_status,
                    'items' => $rule->items,
                ];
            })
            ->values()
            ->all();

		ob_start();
		$rules->printTable();
		$rulesTableHtml = str_replace('images/icons/', '/images/icons/', ob_get_clean());

		ob_start();
		\LeadMax\TrackYourStats\Offer\Rules\Geo::printCountriesAsTable();
		$countryRowsHtml = str_replace('images/icons/', '/images/icons/', ob_get_clean());

		ob_start();
		$offerView->printToSelectBox('geoRedirectOffer');
		$geoRedirectOfferSelect = ob_get_clean();

		ob_start();
		$offerView->printToSelectBox('deviceRedirectOffer');
		$deviceRedirectOfferSelect = ob_get_clean();

		return view('offer.rules', [
			'offer' => $offer,
			'rulesTableHtml' => $rulesTableHtml,
			'countryRowsHtml' => $countryRowsHtml,
			'countryMap' => \LeadMax\TrackYourStats\Offer\Rules\Geo::$countries,
			'geoRules' => $geoRules,
			'deviceRules' => $deviceRules,
			'geoRedirectOfferSelect' => $geoRedirectOfferSelect,
			'deviceRedirectOfferSelect' => $deviceRedirectOfferSelect,
			'redirectOfferMap' => $redirectOfferMap,
			'activeCap' => $activeCap,
			'capAmount' => $capAmount,
            'predefinedGeoRules' => $predefinedGeoRules,
            'predefinedDeviceRules' => $predefinedDeviceRules,
		]);
	}

    public function storePredefinedRule(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'type' => 'required|in:geo,device',
            'name' => 'required|string|min:3|max:120',
            'rule_name' => 'nullable|string|max:120',
            'redirect_offer' => 'nullable|integer|min:0',
            'deny' => 'required|boolean',
            'is_active' => 'required|boolean',
            'cap_amount' => 'nullable|integer|min:0',
            'cap_status' => 'required|boolean',
            'items' => 'required|array|min:1',
        ]);

        if ($payload['type'] === 'geo') {
            foreach ($payload['items'] as $item) {
                if (!is_array($item) || empty($item['country_code'])) {
                    return response()->json(['message' => 'Each geo predefined rule item must include a country code.'], 422);
                }
            }
        }

        if ($payload['type'] === 'device') {
            foreach ($payload['items'] as $item) {
                if (!is_string($item) || trim($item) === '') {
                    return response()->json(['message' => 'Each device predefined rule item must be a valid device name.'], 422);
                }
            }
        }

        $rule = PredefinedOfferRule::query()->create([
            'type' => $payload['type'],
            'name' => trim($payload['name']),
            'rule_name' => isset($payload['rule_name']) ? trim((string) $payload['rule_name']) : null,
            'redirect_offer' => (int) ($payload['redirect_offer'] ?? 0),
            'deny' => (bool) $payload['deny'],
            'is_active' => (bool) $payload['is_active'],
            'cap_amount' => (int) ($payload['cap_amount'] ?? 0),
            'cap_status' => (bool) $payload['cap_status'],
            'items_json' => json_encode(array_values($payload['items'])),
        ]);

        return response()->json([
            'id' => $rule->id,
            'message' => 'Predefined rule saved.',
        ]);
    }

	private function validateOfferRequest(Request $request, bool $requireUsers = true)
	{
		$rules = [
			'offer_name' => 'required|min:3',
			'url' => 'required',
			'offer_type' => 'required',
			'payout' => 'required|numeric',
			'status' => 'required|numeric',
			'is_public' => 'required|numeric',
		];

		if ($requireUsers) {
			$rules['users'] = 'required|array|min:1';
		}

		$this->validate($request, $rules);
	}

	public function create(Request $request)
	{
		$this->validateOfferRequest($request);
		DB::transaction(function () use ($request) {
			$offer = new Offer($request->all());
			if (!$request->has('campaign_id')) {
				$offer->campaign_id = Campaigns::getDefaultCampaignId();
			}
			$offer->offer_timestamp = Carbon::now('UTC')->format('Y-m-d H:i:s');
			$offer->created_by = \LeadMax\TrackYourStats\System\Session::user()->idrep;
			$offer->save();

			$userIds = User::query()
				->withRole(\App\Privilege::ROLE_AFFILIATE)
				->whereIn('rep.idrep', $request->users)
				->pluck('rep.idrep')
				->map(fn ($value) => (int) $value)
				->unique()
				->values();

			if ($userIds->isNotEmpty()) {
				$rows = $userIds->map(fn (int $userId) => [
					'rep_idrep' => $userId,
					'offer_idoffer' => $offer->idoffer,
					'payout' => $offer->payout,
				])->all();

				DB::table('rep_has_offer')->insert($rows);
			}
		});

		return redirect('/offer/manage')->with('message', 'Offer created successfully.');
	}

	public function update(Request $request, $id)
	{
		$this->validateOfferRequest($request, false);

		$offer = Offer::query()->where('idoffer', '=', $id)->firstOrFail();
		$offer->fill($request->only([
			'offer_name',
			'description',
			'url',
			'offer_type',
			'payout',
			'status',
			'is_public',
			'campaign_id',
		]));

		if (!$request->has('campaign_id')) {
			$offer->campaign_id = Campaigns::getDefaultCampaignId();
		}

		$offer->save();

		return redirect('/offer/manage')->with('message', 'Offer updated successfully.');
	}


	public function showOfferURLs()
	{
		$offerURLs = new URLs(Company::loadFromSession());
		$urls = $offerURLs->getOfferUrls()->fetchAll(\PDO::FETCH_ASSOC);
		return view('offer.urls', compact('urls'));
	}

	public function showCreateOfferUrl()
	{
		$activeUrls = OfferURL::query()
		                     ->where('company_id', Company::loadFromSession()->getID())
		                     ->where('status', 1)
		                     ->count();

		return view('offer.url-form', [
			'mode' => 'create',
			'pageTitle' => 'Create Offer URL',
			'formAction' => '/offer/urls/create',
			'offerUrl' => new OfferURL(),
			'activeUrls' => $activeUrls,
		]);
	}

	public function createOfferUrl(Request $request)
	{
		$this->validate($request, [
			'url' => 'required|string|max:255',
			'status' => 'required|in:0,1',
		]);

		OfferURL::query()->create([
			'url' => $request->input('url'),
			'status' => (int) $request->input('status'),
			'company_id' => Company::loadFromSession()->getID(),
			'timestamp' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
		]);

		return redirect('/offer/urls')->with('message', 'Offer URL created successfully.');
	}

	public function showEditOfferUrl($id)
	{
		$offerUrl = OfferURL::query()
		                   ->where('company_id', Company::loadFromSession()->getID())
		                   ->findOrFail($id);

		$activeUrls = OfferURL::query()
		                     ->where('company_id', Company::loadFromSession()->getID())
		                     ->where('status', 1)
		                     ->count();

		return view('offer.url-form', [
			'mode' => 'edit',
			'pageTitle' => 'Edit Offer URL',
			'formAction' => "/offer/urls/{$offerUrl->id}/edit",
			'offerUrl' => $offerUrl,
			'activeUrls' => $activeUrls,
		]);
	}

	public function updateOfferUrl(Request $request, $id)
	{
		$this->validate($request, [
			'url' => 'required|string|max:255',
			'status' => 'required|in:0,1',
		]);

		$offerUrl = OfferURL::query()
		                   ->where('company_id', Company::loadFromSession()->getID())
		                   ->findOrFail($id);

		$offerUrl->url = $request->input('url');
		$offerUrl->status = (int) $request->input('status');
		$offerUrl->save();

		return redirect('/offer/urls')->with('message', 'Offer URL updated successfully.');
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
