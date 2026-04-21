<?php

namespace App\Http\Controllers;

use App\Ban;
use App\Privilege;
use App\User;
use App\Click;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Paginate;
use Illuminate\Support\Facades\Cache;
use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\User\Bonus;
use LeadMax\TrackYourStats\User\Permissions;
use LeadMax\TrackYourStats\User\Privileges;
use LeadMax\TrackYourStats\User\Referrals;
use LeadMax\TrackYourStats\User\ReportPermissions;
use LeadMax\TrackYourStats\User\Tree;
use LeadMax\TrackYourStats\User\User as LegacyUser;

class UserController extends Controller
{

    public function viewManagersAffiliates($id)
    {
        $manager = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->findOrFail($id);


        $affiliates = $manager->users()->withRole(Privilege::ROLE_AFFILIATE)->with('referrer');

        $paginate = new Paginate(request('rpp',10), $affiliates->count());

        $affiliates = $affiliates->paginate(request('rpp', 10));

        return view('user.managers-affiliates', compact('manager', 'affiliates','paginate'));
    }

    public function viewManageUsers()
    {

	    $userType = Session::userType();
	    $canViewUsers = Session::permissions()->can('view_all_users');

        $this->validate(request(), [
            'showInactive' => 'numeric|min:0|max:1'
        ]);

	    $users =
		    ($userType == Privilege::ROLE_ADMIN && $canViewUsers) || $userType == Privilege::ROLE_GOD ?
			    User::withRole(request('role', Privilege::ROLE_AFFILIATE))->with('referrer')
			    :
			    User::myUsers()->withRole(request('role', Privilege::ROLE_AFFILIATE))->with('referrer');

        if (request('showInactive', 0) == 1) {
            $users->where('status', 0);
        } else {
            $users->where('status', 1);
        }
/*
		if (Session::userType() == Privilege::ROLE_ADMIN && (request('role') == null ||  request('role') == '3')) {
			$userId = Session::userID();
			$managers = DB::table('rep')->where('referrer_repid', '=', $userId)->get()->pluck('idrep')->toArray();
			$users->whereIn('referrer_repid', $managers);
		}
		*/
        $users = $users->get();
		$users = $this->getDiffForHumans($users);

        return view('user.manage', compact('users'));
    }

    public function showCreateUser()
    {
        $this->authorizeUserCreation();

        return view('user.form', $this->buildUserFormViewData());
    }

    public function storeUser(Request $request)
    {
        $this->authorizeUserCreation();

        $roleOptions = $this->getRoleOptionsForCurrentUser();
        $allowedRoleIds = array_keys($roleOptions);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:155',
            'last_name' => 'nullable|string|max:155',
            'email' => 'nullable|email|max:155|unique:rep,email',
            'cell_phone' => 'nullable|string|max:155',
            'company_name' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'skype' => 'nullable|string|max:255',
            'user_name' => 'required|string|max:155|unique:rep,user_name',
            'password' => 'required|string|min:5|max:255',
            'confirmpassword' => 'required|string|min:5|max:255|same:password',
            'status' => 'required|in:0,1',
            'priv' => ['required', Rule::in($allowedRoleIds)],
            'referrer_repid' => 'required|integer',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'enable_referral' => 'nullable|boolean',
            'referral_user_id' => 'nullable|integer',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'referral_type' => 'nullable|in:flat,percentage',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $targetRole = (int) $validated['priv'];
        $ownerOptions = $this->getOwnerOptionsForCreate($targetRole);
        if (!$ownerOptions->pluck('idrep')->map(fn ($value) => (int) $value)->contains((int) $validated['referrer_repid'])) {
            return back()->withErrors(['referrer_repid' => 'Select a valid owner for the chosen account type.'])->withInput();
        }

        if ($request->boolean('enable_referral') && $targetRole !== Privilege::ROLE_AFFILIATE) {
            return back()->withErrors(['enable_referral' => 'Referral settings are only available for affiliate accounts.'])->withInput();
        }

        $selectedPermissions = $this->filterSelectedPermissions($validated['permissions'] ?? [], $targetRole);

        $shouldRebuildTree = (int) $validated['status'] === 1;

        $permissionList = Permissions::defaultUserPermissions([], $targetRole);
        foreach ($selectedPermissions as $permissionKey) {
            $permissionList[$permissionKey] = 1;
        }

        $newUserId = DB::transaction(function () use ($validated, $targetRole, $permissionList) {
            $userId = DB::table('rep')->insertGetId([
                'first_name' => $validated['first_name'] ?? '',
                'last_name' => $validated['last_name'] ?? '',
                'cell_phone' => $validated['cell_phone'] ?? '',
                'email' => $validated['email'] ?? '',
                'user_name' => $validated['user_name'],
                'password' => password_hash($validated['password'], PASSWORD_DEFAULT),
                'status' => (int) $validated['status'],
                'referrer_repid' => (int) $validated['referrer_repid'],
                'rep_timestamp' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
                'skype' => $validated['telegram'] ?? $validated['skype'] ?? '',
                'company_name' => $validated['company_name'] ?? '',
            ]);

            DB::table('privileges')->insert([
                'rep_idrep' => $userId,
                'is_god' => $targetRole === Privilege::ROLE_GOD ? 1 : 0,
                'is_admin' => $targetRole === Privilege::ROLE_ADMIN ? 1 : 0,
                'is_manager' => $targetRole === Privilege::ROLE_MANAGER ? 1 : 0,
                'is_rep' => $targetRole === Privilege::ROLE_AFFILIATE ? 1 : 0,
            ]);

            DB::table('permissions')->insert($permissionList + ['aff_id' => $userId]);

            if ($targetRole === Privilege::ROLE_AFFILIATE) {
                DB::table('report_permissions')->insert(['user_id' => $userId]);
            }

            return $userId;
        });

        if ($targetRole === Privilege::ROLE_AFFILIATE) {
            RepHasOffer::assignAffiliateToPublicOffers($newUserId);
        }

        if (
            Session::permissions()->can(Permissions::EDIT_REFERRALS) &&
            $request->boolean('enable_referral') &&
            !empty($validated['referral_user_id']) &&
            !empty($validated['start_date']) &&
            !empty($validated['referral_type']) &&
            array_key_exists('amount', $validated)
        ) {
            Referrals::addReferral($validated['referral_user_id'], $newUserId, [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? '',
                'referral_type' => $validated['referral_type'],
                'payout' => $validated['amount'] ?? 0,
            ]);
        }

        Bonus::assignUsersInheritableBonuses([$newUserId], (int) $validated['referrer_repid']);

        if ($shouldRebuildTree) {
            $this->rebuildUserTree();
        }

        return redirect("/user/{$newUserId}/edit")->with('message', 'User created successfully.');
    }

    public function showEditUser($id)
    {
        $user = User::query()->with('referrer')->findOrFail($id);
        $this->authorizeUserEdit($user);

        return view('user.form', $this->buildUserFormViewData($user));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::query()->with('role')->findOrFail($id);
        $this->authorizeUserEdit($user);

        $targetRole = $user->getRole();
        $canManageRole = $this->canManageUserRoles($user);
        $allowedRoleIds = array_keys($this->getRoleOptionsForCurrentUser());

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:155',
            'last_name' => 'nullable|string|max:155',
            'email' => ['nullable', 'email', 'max:155', Rule::unique('rep', 'email')->ignore($user->idrep, 'idrep')],
            'cell_phone' => 'nullable|string|max:155',
            'company_name' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'skype' => 'nullable|string|max:255',
            'user_name' => [
                Rule::requiredIf(Session::userType() === Privilege::ROLE_GOD),
                'nullable',
                'string',
                'max:155',
                Rule::unique('rep', 'user_name')->ignore($user->idrep, 'idrep'),
            ],
            'password' => 'nullable|string|min:5|max:255',
            'confirmpassword' => 'nullable|string|min:5|max:255|same:password',
            'status' => 'required|in:0,1',
            'priv' => ['nullable', Rule::in($allowedRoleIds)],
            'referrer_repid' => 'nullable|integer',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'referrer_box' => 'nullable|integer',
        ]);

        if ($canManageRole && $user->getRole() !== Privilege::ROLE_GOD && !empty($validated['priv'])) {
            $targetRole = (int) $validated['priv'];
        }

        if ($this->userHasChildren($user) && $targetRole > $user->getRole()) {
            return back()->withErrors(['priv' => 'This user cannot be downgraded while they still have users assigned to them.'])->withInput();
        }

        if ($this->userHasReferralStructure($user) && $targetRole < $user->getRole()) {
            return back()->withErrors(['priv' => 'This user cannot be upgraded while referral structures are attached to the account.'])->withInput();
        }

        if ((Session::userType() === Privilege::ROLE_GOD || Session::userType() === Privilege::ROLE_ADMIN) && $user->getRole() !== Privilege::ROLE_GOD) {
            $ownerOptions = $this->getOwnerOptionsForEdit($targetRole);
            $requestedOwner = (int) ($validated['referrer_repid'] ?? $user->referrer_repid);
            if (!$ownerOptions->pluck('idrep')->map(fn ($value) => (int) $value)->contains($requestedOwner)) {
                return back()->withErrors(['referrer_repid' => 'Select a valid owner for the chosen account type.'])->withInput();
            }
        }

        $selectedPermissions = $this->filterSelectedPermissions($validated['permissions'] ?? [], $targetRole);

        $requestedOwner = (int) ($validated['referrer_repid'] ?? $user->referrer_repid);
        $requestedStatus = (int) ($validated['status'] ?? $user->status);
        $shouldRebuildTree = $this->shouldRebuildUserTree($user, $requestedOwner, $requestedStatus);
        $shouldReassignBonuses = $this->shouldReassignInheritableBonuses($user, $requestedOwner);

        DB::transaction(function () use ($validated, $user, $targetRole, $canManageRole, $selectedPermissions, $shouldRebuildTree, $shouldReassignBonuses) {
            $updatePayload = [
                'first_name' => $validated['first_name'] ?? '',
                'last_name' => $validated['last_name'] ?? '',
                'cell_phone' => $validated['cell_phone'] ?? '',
                'email' => $validated['email'] ?? '',
                'status' => (int) $validated['status'],
                'skype' => $validated['telegram'] ?? $validated['skype'] ?? '',
                'company_name' => $validated['company_name'] ?? '',
            ];

            if (Session::userType() === Privilege::ROLE_GOD) {
                $updatePayload['user_name'] = $validated['user_name'] ?: $user->user_name;
            }

            if ((Session::userType() === Privilege::ROLE_GOD || Session::userType() === Privilege::ROLE_ADMIN) && $user->getRole() !== Privilege::ROLE_GOD) {
                $updatePayload['referrer_repid'] = (int) ($validated['referrer_repid'] ?? $user->referrer_repid);
            }

            if (!empty($validated['password'])) {
                $updatePayload['password'] = password_hash($validated['password'], PASSWORD_DEFAULT);
            }

            DB::table('rep')->where('idrep', $user->idrep)->update($updatePayload);

            if ($canManageRole && $user->getRole() !== Privilege::ROLE_GOD) {
                DB::table('privileges')->where('rep_idrep', $user->idrep)->update([
                    'is_admin' => $targetRole === Privilege::ROLE_ADMIN ? 1 : 0,
                    'is_manager' => $targetRole === Privilege::ROLE_MANAGER ? 1 : 0,
                    'is_rep' => $targetRole === Privilege::ROLE_AFFILIATE ? 1 : 0,
                ]);

                $permissionService = new Permissions();
                $permissionList = Permissions::defaultUserPermissions([], $targetRole);
                foreach ($selectedPermissions as $permissionKey) {
                    $permissionList[$permissionKey] = 1;
                }

                if (!Permissions::permissionsExist($user->idrep)) {
                    $permissionList['aff_id'] = $user->idrep;
                    $permissionService->createPermissions($permissionList);
                } else {
                    $permissionService->updatePermissions($permissionList, $user->idrep);
                }
            }

            if ($shouldRebuildTree) {
                $this->rebuildUserTree();
            }

            if (!empty($validated['referrer_box'])) {
                Referrals::updateReferrer($user->idrep, $validated['referrer_box']);
            }

            $bonusOwner = (int) ($updatePayload['referrer_repid'] ?? $user->referrer_repid);
            if ($shouldReassignBonuses) {
                Bonus::assignUsersInheritableBonuses([$user->idrep], $bonusOwner);
            }
        });

        return redirect("/user/{$user->idrep}/edit")->with('message', 'User updated successfully.');
    }

    public function showUserReferrals($id)
    {
        $referrer = User::query()->findOrFail($id);
        $this->authorizeReferralEdit($referrer);

        $referrals = DB::table('referrals')
            ->leftJoin('rep', 'rep.idrep', '=', 'referrals.aff_id')
            ->where('referrals.referrer_user_id', $referrer->idrep)
            ->select([
                'referrals.referrer_user_id',
                'referrals.aff_id',
                'referrals.start_date',
                'referrals.end_date',
                'referrals.referral_type',
                'referrals.commission_basis',
                'referrals.min_payment_threshhold',
                'referrals.payout',
                'referrals.is_active',
                'rep.user_name',
            ])
            ->orderBy('rep.user_name')
            ->get();

        return view('user.referrals', compact('referrer', 'referrals'));
    }

    public function updateUserReferral(Request $request, $id)
    {
        $referrer = User::query()->findOrFail($id);
        $this->authorizeReferralEdit($referrer);

        $validated = $request->validate([
            'affid' => 'required|integer',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'referral_type' => 'required|in:flat,percentage',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'required|in:active,unactive',
        ]);

        abort_unless(LegacyUser::hasAffiliate((int) $validated['affid']), 403);

        Referrals::updateReferral($referrer->idrep, (int) $validated['affid'], [
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? '',
            'referral_type' => $validated['referral_type'],
            'amount' => $validated['amount'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect("/user/{$referrer->idrep}/referrals")->with('message', 'Referral settings updated successfully.');
    }

    public function deleteUserReferral($id, $affiliateId)
    {
        $referrer = User::query()->findOrFail($id);
        $this->authorizeReferralEdit($referrer);

        abort_unless(LegacyUser::hasAffiliate((int) $affiliateId), 403);

        Referrals::deleteReferralStructure($referrer->idrep, (int) $affiliateId);

        return redirect("/user/{$referrer->idrep}/referrals")->with('message', 'Referral removed successfully.');
    }

    public function showCreateUserReferral($id)
    {
        $referrer = User::query()->findOrFail($id);
        $this->authorizeReferralEdit($referrer);

        $availableAffiliates = DB::table('rep')
            ->join('privileges', function ($join) {
                $join->on('privileges.rep_idrep', '=', 'rep.idrep')
                    ->where('privileges.is_rep', 1);
            })
            ->where('rep.lft', '>', Session::userData()->lft)
            ->where('rep.rgt', '<', Session::userData()->rgt)
            ->where('rep.idrep', '!=', $referrer->idrep)
            ->whereNotIn('rep.idrep', function ($query) {
                $query->select('aff_id')->from('referrals');
            })
            ->groupBy('rep.idrep', 'rep.user_name')
            ->orderBy('rep.user_name')
            ->get(['rep.idrep', 'rep.user_name']);

        return view('user.referral-form', compact('referrer', 'availableAffiliates'));
    }

    public function storeUserReferral(Request $request, $id)
    {
        $referrer = User::query()->findOrFail($id);
        $this->authorizeReferralEdit($referrer);

        $validated = $request->validate([
            'toRefer' => 'required|integer',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'referral_type' => 'required|in:flat,percentage',
            'amount' => 'required|numeric|min:0',
        ]);

        $availableAffiliateIds = DB::table('rep')
            ->join('privileges', function ($join) {
                $join->on('privileges.rep_idrep', '=', 'rep.idrep')
                    ->where('privileges.is_rep', 1);
            })
            ->where('rep.lft', '>', Session::userData()->lft)
            ->where('rep.rgt', '<', Session::userData()->rgt)
            ->where('rep.idrep', '!=', $referrer->idrep)
            ->whereNotIn('rep.idrep', function ($query) {
                $query->select('aff_id')->from('referrals');
            })
            ->pluck('rep.idrep')
            ->map(fn ($value) => (int) $value);

        abort_unless($availableAffiliateIds->contains((int) $validated['toRefer']), 403);

        Referrals::addReferral($referrer->idrep, (int) $validated['toRefer'], [
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? '',
            'referral_type' => $validated['referral_type'],
            'payout' => $validated['amount'],
        ]);

        return redirect("/user/{$referrer->idrep}/referrals")->with('message', 'Referral created successfully.');
    }

    public function viewPendingUsers()
    {
        $users = User::query()
            ->where('referrer_repid', 1)
            ->where('status', 0)
            ->orderByDesc('rep_timestamp')
            ->get();

        return view('user.pending', compact('users'));
    }

    public function showActivatePendingUser($id)
    {
        $user = $this->findPendingAffiliateOrFail($id);
        $assignableManagers = $this->getAssignableManagersForPendingAffiliate();
        $referralOptions = Session::permissions()->can(Permissions::EDIT_REFERRALS)
            ? User::query()->withRole(Privilege::ROLE_AFFILIATE)->myUsers()->orderBy('user_name')->get(['rep.idrep', 'rep.user_name'])
            : collect();

        return view('user.pending-activate', compact('user', 'assignableManagers', 'referralOptions'));
    }

    public function activatePendingUser(Request $request, $id)
    {
        $user = $this->findPendingAffiliateOrFail($id);
        $assignableManagers = $this->getAssignableManagersForPendingAffiliate();

        $validated = $request->validate([
            'referrer_repid' => 'required|integer',
            'enable_referral' => 'nullable|boolean',
            'referral_user_id' => 'nullable|integer',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'referral_type' => 'nullable|in:flat,percentage',
            'amount' => 'nullable|numeric|min:0',
        ]);

        if (!$assignableManagers->pluck('idrep')->map(fn ($value) => (int) $value)->contains((int) $validated['referrer_repid'])) {
            abort(403);
        }

        DB::transaction(function () use ($user, $validated) {
            DB::table('rep')
                ->where('idrep', $user->idrep)
                ->update([
                    'status' => 1,
                    'referrer_repid' => $validated['referrer_repid'],
                ]);

            DB::table('privileges')->insert([
                'rep_idrep' => $user->idrep,
                'is_god' => 0,
                'is_admin' => 0,
                'is_manager' => 0,
                'is_rep' => 1,
            ]);

            DB::table('permissions')->insert(['aff_id' => $user->idrep]);

            DB::table('report_permissions')->insert(['user_id' => $user->idrep]);
        });

        Tree::rebuild_tree(1, 1);
        RepHasOffer::assignAffiliateToPublicOffers($user->idrep);

        if (
            Session::permissions()->can(Permissions::EDIT_REFERRALS) &&
            $request->boolean('enable_referral') &&
            !empty($validated['referral_user_id']) &&
            !empty($validated['start_date']) &&
            !empty($validated['referral_type']) &&
            array_key_exists('amount', $validated)
        ) {
            Referrals::addReferral($validated['referral_user_id'], $user->idrep, [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? '',
                'referral_type' => $validated['referral_type'],
                'payout' => $validated['amount'] ?? 0,
            ]);
        }

        Bonus::assignUsersInheritableBonuses([$user->idrep], $validated['referrer_repid']);

        return redirect('/user/pending')->with('message', config('branding.affiliate.singular') . ' activated successfully.');
    }

    public function viewBannedUsers()
    {
        $bounds = Session::userData();

        $bans = DB::table('banned_users')
            ->join('rep', 'rep.idrep', '=', 'banned_users.user_id')
            ->where('rep.lft', '>', $bounds->lft)
            ->where('rep.rgt', '<', $bounds->rgt)
            ->select([
                'banned_users.user_id',
                'rep.user_name',
                'banned_users.timestamp',
                'banned_users.expires',
                'banned_users.reason',
                'banned_users.status',
            ])
            ->orderByDesc('banned_users.timestamp')
            ->get();

        return view('user.banned', compact('bans'));
    }

    public function showCreateBan($id)
    {
        $user = $this->findOwnedUserForBanOrFail($id);

        if (Ban::query()->where('user_id', $user->idrep)->exists()) {
            return redirect("/user/{$user->idrep}/ban/edit");
        }

        return view('user.ban-form', [
            'user' => $user,
            'ban' => null,
            'mode' => 'create',
            'pageTitle' => 'Ban user',
            'formAction' => "/user/{$user->idrep}/ban",
        ]);
    }

    public function storeBan(Request $request, $id)
    {
        $user = $this->findOwnedUserForBanOrFail($id);

        if (Ban::query()->where('user_id', $user->idrep)->exists()) {
            return redirect("/user/{$user->idrep}/ban/edit");
        }

        $validated = $request->validate([
            'expires' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string',
        ]);

        $ban = new Ban();
        $ban->user_id = $user->idrep;
        $ban->expires = $validated['expires'];
        $ban->reason = $validated['reason'] ?? '';
        $ban->status = 1;
        $ban->timestamp = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $ban->save();

        DB::table('rep')->where('idrep', $user->idrep)->update(['status' => 0]);

        return redirect("/user/{$user->idrep}/ban/edit")->with('message', 'Ban saved successfully.');
    }

    public function showEditBan($id)
    {
        $user = $this->findOwnedUserForBanOrFail($id);
        $ban = Ban::query()->where('user_id', $user->idrep)->firstOrFail();

        return view('user.ban-form', [
            'user' => $user,
            'ban' => $ban,
            'mode' => 'edit',
            'pageTitle' => 'Ban settings',
            'formAction' => "/user/{$user->idrep}/ban/edit",
        ]);
    }

    public function updateBan(Request $request, $id)
    {
        $user = $this->findOwnedUserForBanOrFail($id);
        $ban = Ban::query()->where('user_id', $user->idrep)->firstOrFail();

        $validated = $request->validate([
            'expires' => 'required|date_format:Y-m-d',
            'status' => 'required|in:0,1',
            'reason' => 'nullable|string',
        ]);

        $ban->expires = $validated['expires'];
        $ban->status = (int) $validated['status'];
        $ban->reason = $validated['reason'] ?? '';
        $ban->save();

        return redirect("/user/{$user->idrep}/ban/edit")->with('message', 'Ban settings updated successfully.');
    }

	public function AuthRouteAPI(Request $request){
		return $request->user();
	}

	public function blockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->insert([
			'rep_idrep' => $userID,
			'sub_id'    => $subID,
		]);

		return response()->json(['success' => true]);
	}

	public function unblockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->where('rep_idrep', '=', $userID)->where('sub_id', '=', $subID)->delete();

		return response()->json(['success' => true]);
	}

	public function getUserSubIds($id = null) {
        $affId = $id ?? ($_GET["idrep"] ?? null);
        if (!$affId) {
            return response()->json([]);
        }
		$data = DB::select(
			"SELECT
	        sub_ids.sub_id as subId,
	        CASE WHEN blocked_sub_ids.sub_id IS NULL THEN FALSE ELSE TRUE END AS blocked
		     FROM sub_ids
		     LEFT JOIN blocked_sub_ids ON blocked_sub_ids.sub_id = sub_ids.sub_id
		     WHERE sub_ids.idrep = ?
		     GROUP BY subId", [ $affId ]
		);
		return response()->json($data);
    }

	public function changeAffPayout(Request $request) {
		$message = null;

		$userID = $request->rep;
		$offer = $request->offer_id;
		$payout = $request->payout;

		// TODO: check if already has access or not.

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			$offerAccess = DB::table('rep_has_offer')
			                 ->where('rep_idrep', '=', $userID)
			                 ->where('offer_idoffer', '=', $offer)->get();
			if (count($offerAccess) > 0) {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)
				  ->update([
					  'payout' => $payout
				  ]);
				$success = true;
			} else {
				$success = false;
				$message = "User does not have access to offer yet!";
			}

		} else {
			$success = false;
			$message = "You don't have permissions to do this!";
		}
		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function updateAffOfferAccess(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$access = $request->access;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			if ($access) {
				DB::table('rep_has_offer')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'payout'        => $request->payout
				]);
			} else {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)->delete();
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function editUserOffers(User $user) {
		$userID = $user->idrep;
		$userFName = $user->first_name;

		$offers = DB::table('offer')->where('status', '=', 1)->select('idoffer', 'offer_name', 'payout')->get()->toArray();
        $assignedOffers = DB::table('rep_has_offer')
            ->where('rep_idrep', '=', $userID)
            ->get()
            ->keyBy('offer_idoffer');
        $userOfferCaps = DB::table('user_offer_caps')
            ->where('rep_idrep', '=', $userID)
            ->get()
            ->keyBy('offer_idoffer');

		foreach($offers as $index => $offer ) {
			$affHasOffer = $assignedOffers->get($offer->idoffer);
            $offerCap = $userOfferCaps->get($offer->idoffer);

			if ($affHasOffer) {
				$offers[$index]->has_offer = true;
				$offers[$index]->reppayout = $affHasOffer->payout;
			} else {
				$offers[$index]->has_offer = false;
				$offers[$index]->reppayout = 1.00;
			}

            $offers[$index]->cap_enabled = $offerCap ? (bool) $offerCap->status : false;
            $offers[$index]->cap = $offerCap ? (int) $offerCap->cap : 0;
			$offers[$index]->idrep = $userID;
		}


		return view('user.offers')->with([
            'offers' => $offers,
            'name' => $userFName,
            'managedUser' => $user,
            'canManageOffers' => Session::permissions()->can(Permissions::EDIT_AFFILIATES) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canManageSubIds' => Session::userType() === Privilege::ROLE_GOD && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canLoginAsUser' => Session::userType() !== Privilege::ROLE_AFFILIATE && $user->idrep !== Session::userID(),
        ]);
	}

	public function enableUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$status = $request->status;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();

			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'status' => $status
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'status'        => $status
				]);
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);

	}

	public function setUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$cap = $request->cap;
		$message = "";
		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();
			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'cap' => $cap
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'cap' => $cap
				]);
			}

			$success = true;

		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

    private function getDiffForHumans($users) {

		foreach($users as $key => $user) {
			if($user->rep_timestamp) {
				$user->rep_timestamp = Carbon::parse($user->rep_timestamp)->diffForHumans();
			}
		}

		return $users;
	}

    private function authorizeUserCreation()
    {
        if (Session::userType() === Privilege::ROLE_AFFILIATE || Session::userType() === Privilege::ROLE_UNKNOWN) {
            abort(403);
        }

        if (empty($this->getRoleOptionsForCurrentUser())) {
            abort(403);
        }
    }

    private function authorizeUserEdit(User $user)
    {
        if (Session::userType() === Privilege::ROLE_AFFILIATE) {
            abort_unless($user->idrep === Session::userID(), 403);
            return;
        }

        if ($user->idrep !== Session::userID() && !Session::permissions()->can(Permissions::EDIT_AFFILIATES)) {
            abort(403);
        }

        if (Session::userType() === Privilege::ROLE_MANAGER && $user->idrep !== Session::userID() && !LegacyUser::userOwnsUser(Session::userID(), $user->idrep)) {
            abort(403);
        }
    }

    private function authorizeReferralEdit(User $user)
    {
        abort_unless(Session::permissions()->can(Permissions::EDIT_REFERRALS), 403);
        abort_unless(LegacyUser::hasAffiliate($user->idrep), 403);
    }

    private function buildUserFormViewData(?User $user = null)
    {
        $isEdit = $user !== null;
        $canManageRoles = $isEdit ? $this->canManageUserRoles($user) : true;
        $roleOptions = $this->getRoleOptionsForCurrentUser();
        $selectedRole = (int) old('priv', $isEdit ? $user->getRole() : (array_key_first($roleOptions) ?? Privilege::ROLE_AFFILIATE));
        $selectedPermissions = $isEdit ? $this->getSelectedPermissionsForUser($user->idrep) : old('permissions', []);
        $permissionOptionsByRole = [];
        if (!$isEdit || $canManageRoles) {
            foreach (array_keys($roleOptions) as $roleId) {
                $permissionOptionsByRole[$roleId] = $this->getPermissionOptionsForRole((int) $roleId);
            }
        }

        $currentReferralUserId = $isEdit ? Referrals::findReferrer($user->idrep) : null;

        return [
            'mode' => $isEdit ? 'edit' : 'create',
            'pageTitle' => $isEdit ? 'Edit User' : 'Create User',
            'formAction' => $isEdit ? "/user/{$user->idrep}/edit" : '/user/create',
            'managedUser' => $user,
            'roleOptions' => $roleOptions,
            'selectedRole' => $selectedRole,
            'ownerOptionsByRole' => $this->getOwnerOptionsByRoleForView($isEdit),
            'permissionOptionsByRole' => $permissionOptionsByRole,
            'selectedPermissions' => $selectedPermissions,
            'canManageRoles' => $canManageRoles,
            'canEditUsername' => !$isEdit || Session::userType() === Privilege::ROLE_GOD,
            'canEditOwner' => !$isEdit || (Session::userType() === Privilege::ROLE_GOD || Session::userType() === Privilege::ROLE_ADMIN),
            'canLoginAsUser' => $isEdit && Session::userType() !== Privilege::ROLE_AFFILIATE && $user->idrep !== Session::userID(),
            'canManageOffers' => $isEdit && Session::permissions()->can(Permissions::EDIT_AFFILIATES) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canManageSubIds' => $isEdit && Session::userType() === Privilege::ROLE_GOD && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canEditReferrals' => $isEdit && Session::permissions()->can(Permissions::EDIT_REFERRALS) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'referralOptions' => Session::permissions()->can(Permissions::EDIT_REFERRALS)
                ? User::query()->withRole(Privilege::ROLE_AFFILIATE)->myUsers()->orderBy('rep.user_name')->get(['rep.idrep', 'rep.user_name'])
                : collect(),
            'currentReferralUserId' => $currentReferralUserId,
            'hasChildren' => $isEdit ? $this->userHasChildren($user) : false,
            'hasReferralStructure' => $isEdit ? $this->userHasReferralStructure($user) : false,
            'statsOwnerLabel' => $isEdit && $user->referrer ? $user->referrer->user_name : 'Choose on save',
        ];
    }

    private function getRoleOptionsForCurrentUser(): array
    {
        $options = [];

        if (Session::userType() === Privilege::ROLE_GOD || Session::permissions()->can(Permissions::CREATE_ADMINS)) {
            $options[Privilege::ROLE_ADMIN] = 'Admin';
        }

        if (Session::userType() === Privilege::ROLE_GOD || Session::permissions()->can(Permissions::CREATE_MANAGERS)) {
            $options[Privilege::ROLE_MANAGER] = config('branding.account.singular');
        }

        if (Session::userType() === Privilege::ROLE_GOD || Session::permissions()->can(Permissions::CREATE_AFFILIATES)) {
            $options[Privilege::ROLE_AFFILIATE] = config('branding.affiliate.singular');
        }

        return $options;
    }

    private function getOwnerOptionsByRoleForView(bool $isEdit): array
    {
        $byRole = [];
        foreach (array_keys($this->getRoleOptionsForCurrentUser()) as $roleId) {
            $byRole[$roleId] = ($isEdit ? $this->getOwnerOptionsForEdit((int) $roleId) : $this->getOwnerOptionsForCreate((int) $roleId))
                ->map(fn ($owner) => ['idrep' => (int) $owner->idrep, 'user_name' => $owner->user_name])
                ->values()
                ->all();
        }

        return $byRole;
    }

    private function getOwnerOptionsForCreate(int $targetRole)
    {
        return match ($targetRole) {
            Privilege::ROLE_ADMIN => $this->getGodOwnersForCreate(),
            Privilege::ROLE_MANAGER => $this->getAdminOwnersForCreate(),
            Privilege::ROLE_AFFILIATE => $this->getManagerOwnersForCreate(),
            default => collect(),
        };
    }

    private function getOwnerOptionsForEdit(int $targetRole)
    {
        if (!(Session::userType() === Privilege::ROLE_GOD || Session::userType() === Privilege::ROLE_ADMIN)) {
            return collect();
        }

        return match ($targetRole) {
            Privilege::ROLE_ADMIN => $this->getOwnersByPrivilegeColumn('is_god'),
            Privilege::ROLE_MANAGER => $this->getOwnersByPrivilegeColumn('is_admin'),
            Privilege::ROLE_AFFILIATE => $this->getOwnersByPrivilegeColumn('is_manager'),
            default => collect(),
        };
    }

    private function getGodOwnersForCreate()
    {
        return $this->getOwnersByPrivilegeColumn('is_god');
    }

    private function getAdminOwnersForCreate()
    {
        if (Session::userType() === Privilege::ROLE_GOD) {
            return $this->getOwnersByPrivilegeColumn('is_admin');
        }

        if (Session::userType() === Privilege::ROLE_ADMIN) {
            return collect([(object) ['idrep' => Session::userID(), 'user_name' => Session::userData()->user_name]]);
        }

        $parentAdmin = User::query()->find(Session::userData()->referrer_repid);
        return $parentAdmin ? collect([(object) ['idrep' => $parentAdmin->idrep, 'user_name' => $parentAdmin->user_name]]) : collect();
    }

    private function getManagerOwnersForCreate()
    {
        if (Session::userType() === Privilege::ROLE_GOD) {
            return $this->getOwnersByPrivilegeColumn('is_manager');
        }

        if (Session::userType() === Privilege::ROLE_ADMIN) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->myUsers()
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        return collect([(object) ['idrep' => Session::userID(), 'user_name' => Session::userData()->user_name]]);
    }

    private function getOwnersByPrivilegeColumn(string $column)
    {
        return DB::table('rep')
            ->join('privileges', 'privileges.rep_idrep', '=', 'rep.idrep')
            ->where("privileges.{$column}", 1)
            ->where('rep.status', 1)
            ->orderBy('rep.user_name')
            ->get(['rep.idrep', 'rep.user_name']);
    }

    private function getPermissionOptionsForRole(int $role): array
    {
        $sessionPermissions = Session::permissions();
        $options = [];

        foreach (Permissions::$permissionsArray as $permission => $details) {
            if ($permission === 'aff_id') {
                continue;
            }

            if (in_array($permission, $sessionPermissions->affiliateOnlyPermissions ?? [], true)) {
                $options[$permission] = $details['description'];
                continue;
            }

            if (!$sessionPermissions->can($permission)) {
                continue;
            }

            if (isset($details['allowed_user_types'])) {
                if (!in_array(Session::userType(), $details['allowed_user_types'], true)) {
                    continue;
                }
                if (!in_array($role, $details['allowed_user_types'], true)) {
                    continue;
                }
            }

            if (isset($details['required_permissions'])) {
                $missing = collect($details['required_permissions'])->contains(fn ($required) => !$sessionPermissions->can($required));
                if ($missing) {
                    continue;
                }
            }

            $options[$permission] = $details['description'];
        }

        return $options;
    }

    private function getSelectedPermissionsForUser(int $userId): array
    {
        $row = (array) DB::table('permissions')->where('aff_id', $userId)->first();
        if (empty($row)) {
            return [];
        }

        return collect($row)
            ->filter(fn ($value, $key) => $key !== 'aff_id' && (int) $value === 1)
            ->keys()
            ->values()
            ->all();
    }

    private function filterSelectedPermissions(array $selectedPermissions, int $role): array
    {
        $allowed = array_keys($this->getPermissionOptionsForRole($role));

        return array_values(array_intersect($selectedPermissions, $allowed));
    }

    private function canManageUserRoles(User $user): bool
    {
        return in_array(Session::userType(), [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true)
            && $user->getRole() !== Privilege::ROLE_GOD
            && !empty($this->getRoleOptionsForCurrentUser());
    }

    private function userHasChildren(User $user): bool
    {
        return Tree::findChildren((int) $user->lft, (int) $user->rgt) !== 0;
    }

    private function rebuildUserTree(): void
    {
        Tree::rebuild_tree(1, 1);
    }

    private function shouldRebuildUserTree(User $user, int $requestedOwner, int $requestedStatus): bool
    {
        return (int) $user->referrer_repid !== $requestedOwner
            || (int) $user->status !== $requestedStatus;
    }

    private function shouldReassignInheritableBonuses(User $user, int $requestedOwner): bool
    {
        return (int) $user->referrer_repid !== $requestedOwner;
    }

    private function userHasReferralStructure(User $user): bool
    {
        return $user->getRole() === Privilege::ROLE_AFFILIATE
            && (new Referrals($user->idrep))->hasReferrals();
    }

    private function findPendingAffiliateOrFail($id)
    {
        return User::query()
            ->where('idrep', $id)
            ->where('referrer_repid', 1)
            ->where('status', 0)
            ->firstOrFail();
    }

    private function findOwnedUserForBanOrFail($id)
    {
        $user = User::query()->findOrFail($id);

        if (!LegacyUser::userOwnsUser(Session::userID(), $user->idrep)) {
            abort(403);
        }

        return $user;
    }

    private function getAssignableManagersForPendingAffiliate()
    {
        if (Session::userType() === Privilege::ROLE_GOD) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        if (Session::userType() === Privilege::ROLE_ADMIN) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->myUsers()
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        return collect([
            (object) [
                'idrep' => Session::userID(),
                'user_name' => Session::userData()->user_name,
            ],
        ]);
    }
}
