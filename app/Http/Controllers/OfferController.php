<?php

namespace App\Http\Controllers;


use App\Campaign;
use App\Company;
use App\Offer;
use App\OfferURL;
use App\PredefinedOfferRule;
use App\Privilege;
use App\Services\BrandingLabels;
use App\Support\CurrentUserSession;
use App\Support\UserDomain\PostBackURLs\ConversionPostBackURL;
use App\Support\UserDomain\PostBackURLs\DeductionPostBackURL;
use App\Support\OfferDomain\Rules\Handlers\Device as DeviceRuleHandler;
use App\Support\UserDomain\PostBackURLs\FreePostBackURL;
use App\Support\OfferDomain\Campaigns;
use App\Support\OfferDomain\Rules\Handlers\Geo as GeoRuleHandler;
use App\Support\OfferDomain\Rules\Handlers\NoneUnique as NoneUniqueRuleHandler;
use App\Support\OfferDomain\Offer as OfferSupport;
use App\Support\Notifications;
use App\Support\OfferDomain\Rules as OfferRules;
use App\Support\OfferDomain\Rules\Geo as GeoRule;
use App\Support\OfferDomain\View as OfferView;
use App\Support\OfferDomain\RepHasOffer;
use App\Support\UserDomain\User as UserSupport;
use App\Support\RequestContext;
use App\User;
use App\UserOffer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule as ValidationRule;

class OfferController extends Controller
{

	public function requestOffer($id)
	{
		$result = RepHasOffer::requestOffer($id, CurrentUserSession::id());
		return response()->json($result);
	}

    public function approveRequest($id, $user)
    {
        $offerId = (int) $id;
        $userId = (int) $user;

        abort_unless(UserSupport::hasAffiliate($userId), 403, 'You do not have access to this user.');

        $offer = Offer::query()->findOrFail($offerId);

        if (UserOffer::query()->where('offer_idoffer', '=', $offerId)->where('rep_idrep', '=', $userId)->exists()) {
            return redirect('/notifications')->with('message', 'That user already has access to this offer.');
        }

        if (!RepHasOffer::assignAffiliateToOffer($offerId, $userId)) {
            return redirect('/notifications')->withErrors(['offer' => 'Error assigning user to offer.']);
        }

        Notifications::sendNotification(
            $userId,
            1,
            "Offer '{$offer->offer_name}' approved.",
            "Offer {$offer->offer_name} was approved. <br/> This is an automated message."
        );

        return redirect('/notifications')->with('message', 'Successfully assigned offer.');
    }

    public function showPostback($id)
    {
        $offer = $this->findAffiliateOfferOrFail((int) $id);
        $userId = CurrentUserSession::id();

        return view('offer.postback', [
            'offer' => $offer,
            'conversionPostback' => old('postback_url', (string) (new ConversionPostBackURL($userId, $offer->idoffer))->getOfferSpecificURL()),
            'freeSignUpPostback' => old('free_sign_up_url', (string) (new FreePostBackURL($userId, $offer->idoffer))->getOfferSpecificURL()),
            'deductionPostback' => old('deduction_url', (string) (new DeductionPostBackURL($userId, $offer->idoffer))->getOfferSpecificURL()),
        ]);
    }

    public function updatePostback(Request $request, $id)
    {
        $offer = $this->findAffiliateOfferOrFail((int) $id);
        $userId = CurrentUserSession::id();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
            'free_sign_up_url' => 'nullable|string|max:255',
            'deduction_url' => 'nullable|string|max:255',
        ]);

        (new ConversionPostBackURL($userId, $offer->idoffer))
            ->updateOfferURL(trim((string) ($validated['postback_url'] ?? '')));

        (new FreePostBackURL($userId, $offer->idoffer))
            ->updateOfferURL(trim((string) ($validated['free_sign_up_url'] ?? '')));

        (new DeductionPostBackURL($userId, $offer->idoffer))
            ->updateOfferURL(trim((string) ($validated['deduction_url'] ?? '')));

        return redirect("/offer/{$offer->idoffer}/postback")->with('message', 'Offer postbacks updated successfully.');
    }

    public function storeGeoRule(Request $request): JsonResponse
    {
        $data = $this->decodeRulePayload($request->input('data'));
        $offerId = (int) ($data[0] ?? 0);

        if ($offerId <= 0 || !$this->userCanManageOfferRules($offerId)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        if (!$this->hasRuleItems($data, 4)) {
            return response()->json(['message' => 'Add at least one country before saving this rule.'], 422);
        }

        (new GeoRuleHandler($data))->createRule();

        return response()->json(['message' => 'Geo rule created.']);
    }

    public function updateGeoRule(Request $request, int $rule): JsonResponse
    {
        $ruleRecord = $this->findRuleOrFail($rule, 'geo');

        if (!$this->userCanManageOfferRules((int) $ruleRecord->offer_idoffer)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        $ruleData = $this->decodeRuleData($request->input('ruleData'));
        $ruleData->ruleID = $rule;
        $countryList = $this->decodeRulePayload($request->input('data'));

        if (!$this->hasRuleItems($countryList, 0)) {
            return response()->json(['message' => 'Add at least one country before saving this rule.'], 422);
        }

        (new GeoRuleHandler((string) $rule))->updateRule($ruleData, $countryList);

        return response()->json(['message' => 'Geo rule updated.']);
    }

    public function showGeoRule(Request $request, int $rule): JsonResponse
    {
        $ruleRecord = $this->findRuleOrFail($rule, 'geo');

        if (!$this->userCanManageOfferRules((int) $ruleRecord->offer_idoffer)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        if ($request->boolean('getISOs')) {
            return response()->json($this->geoRuleCountries($rule));
        }

        return response()->json([
            'name' => $ruleRecord->name,
            'redirectOffer' => (int) $ruleRecord->redirect_offer,
            'is_active' => (int) $ruleRecord->is_active,
            'deny' => (int) $ruleRecord->deny,
        ]);
    }

    public function storeDeviceRule(Request $request): JsonResponse
    {
        $data = $this->decodeRulePayload($request->input('data'));
        $offerId = (int) ($data[0] ?? 0);

        if ($offerId <= 0 || !$this->userCanManageOfferRules($offerId)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        if (!$this->hasRuleItems($data, 6)) {
            return response()->json(['message' => 'Add at least one device before saving this rule.'], 422);
        }

        (new DeviceRuleHandler($data))->createRule();

        return response()->json(['message' => 'Device rule created.']);
    }

    public function updateDeviceRule(Request $request, int $rule): JsonResponse
    {
        $ruleRecord = $this->findRuleOrFail($rule, 'device');

        if (!$this->userCanManageOfferRules((int) $ruleRecord->offer_idoffer)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        $ruleData = $this->decodeRuleData($request->input('ruleData'));
        $ruleData->ruleID = $rule;
        $deviceList = $this->decodeRulePayload($request->input('data'));

        if (!$this->hasRuleItems($deviceList, 0)) {
            return response()->json(['message' => 'Add at least one device before saving this rule.'], 422);
        }

        (new DeviceRuleHandler((string) $rule))->updateRule($ruleData, $deviceList);

        return response()->json(['message' => 'Device rule updated.']);
    }

    public function showDeviceRule(Request $request, int $rule): JsonResponse
    {
        $ruleRecord = $this->findRuleOrFail($rule, 'device');

        if (!$this->userCanManageOfferRules((int) $ruleRecord->offer_idoffer)) {
            return response()->json(['message' => 'You do not have access to this offer.'], 403);
        }

        if ($request->boolean('getDevices')) {
            return response()->json($this->deviceRuleDevices($rule));
        }

        return response()->json([
            'name' => $ruleRecord->name,
            'redirectOffer' => (int) $ruleRecord->redirect_offer,
            'is_active' => (int) $ruleRecord->is_active,
            'deny' => (int) $ruleRecord->deny,
            'capAmount' => (int) $ruleRecord->cap,
            'capStatus' => (int) $ruleRecord->cap_status,
        ]);
    }

	public function dupe($id)
	{
		if (OfferSupport::duplicateOffer($id)) {
			$message = 'Success!';
		} else {
			$message = 'Oh noes!';
		}

		return back()->with(compact('message'));
	}

	public function delete($id)
	{
		OfferSupport::deleteOffer($id);

		return back();
	}

	public function showManage(Request $request)
	{
		$data = array();

		$this->validate($request, [
			'showInactive' => 'numeric|min:0|max:1'
		]);

		$urls = \App\Company::instance()->first()->offerUrls()->where('status',1)->get();
		/* @var $urls Collection */
		if ($urls->isEmpty()) {
			$url = new OfferURL();
			$url->url = RequestContext::httpHost();
			$urls->add($url);
		}
		$urls = $urls->pluck('url')->toArray();
		$data['urls'] = $urls;


		$currentUserContext = CurrentUserSession::snapshot();
		$sessionUser = $currentUserContext->user();
		$sessionUserId = $currentUserContext->id;
		$sessionUserType = $currentUserContext->type;
		$permissions = $currentUserContext->permissions;
		$canCreateOffers = $permissions->can('create_offers');
		$canEditAffiliates = $permissions->can('edit_affiliates');
		$canEditOfferRules = $permissions->can('edit_offer_rules');
		$canViewPayouts = $permissions->can('view_payouts');
		$isAffiliate = $sessionUserType == Privilege::ROLE_AFFILIATE;
		$isManager = $sessionUserType == Privilege::ROLE_MANAGER;
		$isGod = $sessionUserType == Privilege::ROLE_GOD;
		$showPayoutColumn = $isGod || $canViewPayouts || $isManager;

		$status = $request->query('showInactive', 0) == 1 ? 0 : 1;
		$offers = $sessionUser->offers()
		                                                        ->where('offer.status','=', $status)
		                                                        ->leftJoin('campaigns', 'offer.campaign_id', '=', 'campaigns.id')
		                                                        ->select('offer.*', 'campaigns.name as campaign_name');

		if ($isAffiliate) {
			$offers = $offers->leftJoin('bonus_offers', 'bonus_offers.offer_id', '=', 'offer.idoffer')->get();
			$data['requestableOffers'] = Offer::where('is_public', OfferSupport::VISIBILITY_REQUESTABLE)
			                                  ->whereRaw('offer.idoffer NOT IN (SELECT offer_idoffer FROM rep_has_offer WHERE rep_has_offer.rep_idrep = ' . $sessionUserId . ')')->get();
		} else {
			$offers = $offers->get();
		}

		foreach ($offers as $offer) {
			$offer["offer_name"] = htmlspecialchars($offer["offer_name"]);
		}

		$data = array_merge(compact(
			'canCreateOffers',
			'canEditAffiliates',
			'canEditOfferRules',
			'canViewPayouts',
			'isAffiliate',
			'isGod',
			'isManager',
			'offers',
			'sessionUserId',
			'sessionUserType',
			'showPayoutColumn'
		), $data);
		return view('offer.manage', $data)->with(['data' => $data]);
	}

	public function showCreate(Request $request)
	{
		$offer = $request->session()->get('offer') ?: new Offer();
		$campaigns = Campaign::query()->orderBy('name')->get();

		return view('offer.create')->with([
			'mode' => 'create',
			'pageTitle' => 'Create Offer',
			'pageHeading' => 'Create a new offer',
			'pageCopy' => 'Launch a new offer, choose how it appears in the directory, and assign it to the right ' . strtolower(BrandingLabels::affiliates()) . ' from the same screen.',
			'formAction' => '/offer/create',
			'submitLabel' => 'Create offer',
			'offer' => $offer,
			'campaigns' => $campaigns,
		]);
	}

	public function getAssignableUsers(Request $request)
	{
		return User::withRole($request->input('user_type') === Privilege::ROLE_MANAGER ? Privilege::ROLE_MANAGER : Privilege::ROLE_AFFILIATE)
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
			'pageCopy' => 'Update the main offer details, payout, visibility, and advertiser assignment.',
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

		$permissions = CurrentUserSession::permissions();

		return view('offer.show', [
			'offer' => $offer,
			'assignedUsers' => $offer->affiliates,
			'canCreateOffers' => $permissions->can('create_offers'),
			'canEditOfferRules' => $permissions->can('edit_offer_rules'),
		]);
	}

	public function showRules($id)
	{
		$offer = Offer::query()->where('idoffer', '=', $id)->firstOrFail();
		$rules = new OfferRules($offer->idoffer);
		$offerView = new OfferView(CurrentUserSession::type());
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
		GeoRule::printCountriesAsTable();
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
			'countryMap' => GeoRule::$countries,
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

    public function showCreateNoneUniqueRule($id)
    {
        $offer = Offer::query()->where('idoffer', '=', (int) $id)->firstOrFail();

        abort_unless($this->userCanManageOfferRules((int) $offer->idoffer), 403, 'You do not have access to this offer.');

        return view('offer.none-unique-rule', [
            'mode' => 'create',
            'offer' => $offer,
            'rule' => null,
            'redirectOffers' => $this->redirectOfferOptionsForRules((int) $offer->idoffer),
            'action' => "/offer/rules/{$offer->idoffer}/none-unique/create",
        ]);
    }

    public function storeNoneUniqueRule(Request $request, $id)
    {
        $offer = Offer::query()->where('idoffer', '=', (int) $id)->firstOrFail();

        abort_unless($this->userCanManageOfferRules((int) $offer->idoffer), 403, 'You do not have access to this offer.');

        $payload = $this->validateNoneUniqueRuleRequest($request, (int) $offer->idoffer);

        $rule = new NoneUniqueRuleHandler();
        $rule->name = trim($payload['name']);
        $rule->redirect_offer = (int) $payload['redirect_offer'];
        $rule->offer_idoffer = (int) $offer->idoffer;
        $rule->is_active = (int) $payload['is_active'];
        $rule->save();

        return redirect("/offer/rules/{$offer->idoffer}")->with('message', 'None-unique rule created.');
    }

    public function showEditNoneUniqueRule($rule)
    {
        $ruleRecord = $this->findRuleOrFail((int) $rule, 'none_unique');
        $offer = Offer::query()->where('idoffer', '=', (int) $ruleRecord->offer_idoffer)->firstOrFail();

        abort_unless($this->userCanManageOfferRules((int) $offer->idoffer), 403, 'You do not have access to this offer.');

        return view('offer.none-unique-rule', [
            'mode' => 'edit',
            'offer' => $offer,
            'rule' => NoneUniqueRuleHandler::loadFromId((int) $rule),
            'redirectOffers' => $this->redirectOfferOptionsForRules((int) $offer->idoffer),
            'action' => "/offer/rules/none-unique/{$rule}/edit",
        ]);
    }

    public function updateNoneUniqueRule(Request $request, $rule)
    {
        $ruleRecord = $this->findRuleOrFail((int) $rule, 'none_unique');

        abort_unless($this->userCanManageOfferRules((int) $ruleRecord->offer_idoffer), 403, 'You do not have access to this offer.');

        $payload = $this->validateNoneUniqueRuleRequest($request, (int) $ruleRecord->offer_idoffer);
        $noneUniqueRule = NoneUniqueRuleHandler::loadFromId((int) $rule);
        $noneUniqueRule->name = trim($payload['name']);
        $noneUniqueRule->redirect_offer = (int) $payload['redirect_offer'];
        $noneUniqueRule->is_active = (int) $payload['is_active'];
        $noneUniqueRule->update();

        return redirect("/offer/rules/{$noneUniqueRule->offer_idoffer}")->with('message', 'None-unique rule updated.');
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

    private function decodeRulePayload($payload): array
    {
        $decoded = json_decode((string) $payload);

        abort_if(!is_array($decoded), 422, 'Rule payload must be a valid JSON array.');

        return $decoded;
    }

    private function decodeRuleData($payload): \stdClass
    {
        $decoded = json_decode((string) $payload);

        abort_if(!$decoded instanceof \stdClass, 422, 'Rule data must be a valid JSON object.');

        return $decoded;
    }

    private function findRuleOrFail(int $ruleId, string $type)
    {
        $rule = DB::table('rule')
            ->where('idrule', '=', $ruleId)
            ->where('type', '=', $type)
            ->first();

        abort_if(!$rule, 404, 'Rule not found.');

        return $rule;
    }

    private function hasRuleItems(array $payload, int $metadataCount): bool
    {
        return count($payload) > $metadataCount;
    }

    private function geoRuleCountries(int $ruleId): array
    {
        return DB::table('geo_rule')
            ->join('country_list', 'country_list.geo_rule_idgeo_rule', '=', 'geo_rule.idgeo_rule')
            ->where('geo_rule.rule_idrule', '=', $ruleId)
            ->select(['country_list.country_code', 'country_list.cap_status', 'country_list.cap'])
            ->get()
            ->map(fn ($country) => [
                'country_code' => $country->country_code,
                'cap_status' => (int) $country->cap_status,
                'cap' => (int) $country->cap,
            ])
            ->all();
    }

    private function deviceRuleDevices(int $ruleId): array
    {
        return DB::table('device_rule')
            ->join('device_list', 'device_list.device_rule_iddevice_rule', '=', 'device_rule.iddevice_rule')
            ->where('device_rule.rule_idrule', '=', $ruleId)
            ->pluck('device_list.device_type')
            ->filter()
            ->values()
            ->all();
    }

    private function validateNoneUniqueRuleRequest(Request $request, int $offerId): array
    {
        $redirectOfferIds = collect($this->redirectOfferOptionsForRules($offerId))
            ->pluck('idoffer')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $request->validate([
            'name' => 'required|string|max:255',
            'redirect_offer' => ['required', 'integer', 'min:1', ValidationRule::in($redirectOfferIds)],
            'is_active' => 'required|boolean',
        ]);
    }

    private function redirectOfferOptionsForRules(int $excludeOfferId): array
    {
        $offerView = new OfferView(CurrentUserSession::type());

        return collect($offerView->getUsersQuery()->fetchAll(\PDO::FETCH_OBJ))
            ->filter(fn ($offer) => (int) $offer->idoffer !== $excludeOfferId)
            ->values()
            ->all();
    }

    private function userCanManageOfferRules(int $offerId): bool
    {
        return RepHasOffer::noneRepOwnOffer(
            $offerId,
            CurrentUserSession::id()
        );
    }

    private function findAffiliateOfferOrFail(int $offerId): Offer
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->type === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');

        $offer = Offer::query()->findOrFail($offerId);
        $hasOffer = UserOffer::query()
            ->where('rep_idrep', '=', $currentUserContext->id)
            ->where('offer_idoffer', '=', $offerId)
            ->exists();

        abort_unless($hasOffer, 404);

        return $offer;
    }

	private function validateOfferRequest(Request $request, bool $requireUsers = true)
	{
		$rules = [
			'offer_name' => 'required|min:3',
			'url' => 'required',
			'offer_type' => 'required',
			'payout' => 'required|numeric|min:0',
            'affiliate_payout' => 'nullable|numeric|min:0',
            'manager_payout' => 'nullable|numeric|min:0',
            'admin_payout' => 'nullable|numeric|min:0',
			'status' => 'required|numeric',
			'is_public' => 'required|numeric',
		];

		if ($requireUsers) {
			$rules['users'] = 'required|array|min:1';
		}

		$this->validate($request, $rules);
	}

    private function normalizedOfferPayload(Request $request): array
    {
        $payload = $request->only([
            'offer_name',
            'description',
            'url',
            'offer_type',
            'payout',
            'affiliate_payout',
            'manager_payout',
            'admin_payout',
            'status',
            'is_public',
            'campaign_id',
        ]);

        foreach (['affiliate_payout', 'manager_payout', 'admin_payout'] as $key) {
            if ($payload[$key] === '' || $payload[$key] === null) {
                $payload[$key] = null;
            }
        }

        return $payload;
    }

	public function create(Request $request)
	{
		$this->validateOfferRequest($request);
		DB::transaction(function () use ($request) {
			$offer = new Offer($this->normalizedOfferPayload($request));
			if (!$request->has('campaign_id')) {
				$offer->campaign_id = Campaigns::getDefaultCampaignId();
			}
			$offer->offer_timestamp = Carbon::now('UTC')->format('Y-m-d H:i:s');
			$offer->created_by = CurrentUserSession::user()->idrep;
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
					'payout' => null,
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
		$offer->fill($this->normalizedOfferPayload($request));

		if (!$request->has('campaign_id')) {
			$offer->campaign_id = Campaigns::getDefaultCampaignId();
		}

		$offer->save();

		return redirect('/offer/manage')->with('message', 'Offer updated successfully.');
	}


	public function showOfferURLs()
	{
		$urls = OfferURL::query()
			->where('company_id', $this->currentCompany()->getID())
			->get()
			->toArray();

		return view('offer.urls', compact('urls'));
	}

	public function showCreateOfferUrl()
	{
		$activeUrls = OfferURL::query()
		                     ->where('company_id', $this->currentCompany()->getID())
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
			'company_id' => $this->currentCompany()->getID(),
			'timestamp' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
		]);

		return redirect('/offer/urls')->with('message', 'Offer URL created successfully.');
	}

	public function showEditOfferUrl($id)
	{
		$companyId = $this->currentCompany()->getID();
		$offerUrl = OfferURL::query()
		                   ->where('company_id', $companyId)
		                   ->findOrFail($id);

		$activeUrls = OfferURL::query()
		                     ->where('company_id', $companyId)
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
		                   ->where('company_id', $this->currentCompany()->getID())
		                   ->findOrFail($id);

		$offerUrl->url = $request->input('url');
		$offerUrl->status = (int) $request->input('status');
		$offerUrl->save();

		return redirect('/offer/urls')->with('message', 'Offer URL updated successfully.');
	}

	private function currentCompany(): Company
	{
		$company = Company::instance()->first();

		abort_unless($company, 404, 'Company install not found.');

		return $company;
	}

	public function massAssign(Request $request)
	{
		$this->validate($request, [
			'users' => 'required|array',
			'offers' => 'required|array'
		]);
		RepHasOffer::massAssignUsers($request->post('users'), $request->post('offers'),
			$request->input('role', 3));

		if ($request->has("updatePayouts")) {
			RepHasOffer::massUpdateOfferPayouts($request->post('offers'));
		}

		return back()->with('message', 'Success!');
	}

    public function showAccess($id)
    {
        $offer = $this->findManageableAccessOfferOrFail((int) $id);
        $users = $this->ownedAffiliateAccessRows((int) $offer->idoffer);
        $assignedCount = $users->where('has_offer', true)->count();

        return view('offer.access', [
            'offer' => $offer,
            'users' => $users,
            'assignedCount' => $assignedCount,
        ]);
    }

    public function updateAccess(Request $request, $id)
    {
        $offer = $this->findManageableAccessOfferOrFail((int) $id);
        $users = $this->ownedAffiliateAccessRows((int) $offer->idoffer);
        $ownedUserIds = $users->pluck('idrep')->map(fn ($userId) => (int) $userId)->all();
        $selectedUserIds = collect($request->input('userList', []))
            ->map(fn ($userId) => (int) $userId)
            ->intersect($ownedUserIds)
            ->values()
            ->all();
        $assignedUserIds = $users
            ->where('has_offer', true)
            ->pluck('idrep')
            ->map(fn ($userId) => (int) $userId)
            ->all();
        $unassignedUserIds = array_values(array_diff($assignedUserIds, $selectedUserIds));

        if (!empty($selectedUserIds)) {
            RepHasOffer::massAssignAffiliates($selectedUserIds, [(int) $offer->idoffer]);
        }

        if (!empty($unassignedUserIds)) {
            RepHasOffer::unAssignAffiliates($unassignedUserIds, (int) $offer->idoffer);
        }

        return redirect("/offer/{$offer->idoffer}/access")->with('message', 'Offer access updated successfully.');
    }

	public function showMassAssign(Request $request)
	{
		$users = User::myUsers()->withRole($request->query('role', 3))->get();

		$offers = CurrentUserSession::user()->offers()->get();

		return view('offer.mass-assign', compact('users', 'offers'));
	}

    private function findManageableAccessOfferOrFail(int $offerId): Offer
    {
        $offer = Offer::query()->where('idoffer', '=', $offerId)->firstOrFail();

        abort_unless($this->userCanManageOfferRules((int) $offer->idoffer), 403, 'You do not have access to this offer.');
        abort_if($offer->parent !== null, 404);

        return $offer;
    }

    private function ownedAffiliateAccessRows(int $offerId)
    {
        $assignedAffiliateIds = collect(
            RepHasOffer::queryGetAffiliatesAssignedToOffer($offerId)->fetchAll(\PDO::FETCH_OBJ)
        )
            ->pluck('idrep')
            ->map(fn ($userId) => (int) $userId)
            ->all();

        return collect(UserSupport::selectAllOwnedAffiliates()->fetchAll(\PDO::FETCH_ASSOC))
            ->map(function (array $user) use ($assignedAffiliateIds) {
                $user['idrep'] = (int) $user['idrep'];
                $user['has_offer'] = in_array($user['idrep'], $assignedAffiliateIds, true);

                return (object) $user;
            });
    }

}
