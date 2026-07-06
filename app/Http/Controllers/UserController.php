<?php

namespace App\Http\Controllers;

use App\Ban;
use App\Privilege;
use App\Services\BrandingLabels;
use App\User;
use App\Click;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyBonus as Bonus;
use App\Support\LegacyPaginate as Paginate;
use App\Support\LegacyRepHasOffer as RepHasOffer;
use App\Support\LegacyTree as Tree;
use Illuminate\Support\Facades\Cache;
use App\Support\LegacyPermissions as Permissions;
use App\Support\LegacyPrivileges as Privileges;
use App\Support\LegacyReferrals as Referrals;
use App\Support\LegacyReportPermissions as ReportPermissions;
use App\Support\LegacyUser;

class UserController extends Controller
{

    public function viewManagersAffiliates(Request $request, $id)
    {
        $manager = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->findOrFail($id);


        $affiliates = $manager->users()->withRole(Privilege::ROLE_AFFILIATE)->with('referrer');
        $rowsPerPage = $request->query('rpp', 10);

        $paginate = new Paginate($rowsPerPage, $affiliates->count());

        $affiliates = $affiliates->paginate($rowsPerPage);

        return view('user.managers-affiliates', compact('manager', 'affiliates','paginate', 'rowsPerPage'));
    }

    public function viewManageUsers(Request $request)
    {

	    $currentUserContext = CurrentUserSession::snapshot();
	    $userType = $currentUserContext->type;
	    $permissions = $currentUserContext->permissions;
	    $canViewUsers = $permissions->can('view_all_users');

        $request->validate([
            'showInactive' => 'numeric|min:0|max:1'
        ]);
        $role = (int) $request->query('role', Privilege::ROLE_AFFILIATE);
        $showInactive = (int) $request->query('showInactive', 0) === 1;

	    $users =
		    ($userType == Privilege::ROLE_ADMIN && $canViewUsers) || $userType == Privilege::ROLE_GOD ?
			    User::withRole($role)->with('referrer')
			    :
			    User::myUsers()->withRole($role)->with('referrer');

        if ($showInactive) {
            $users->where('status', 0);
        } else {
            $users->where('status', 1);
        }
        $users = $users->get();
		//$users = $this->getDiffForHumans($users);

        return view('user.manage', [
            'canBanUsers' => $permissions->can(Permissions::BAN_USERS),
            'canCreateAffiliates' => $permissions->can(Permissions::CREATE_AFFILIATES),
            'canCreateManagers' => $permissions->can(Permissions::CREATE_MANAGERS),
            'canEditAffiliates' => $permissions->can(Permissions::EDIT_AFFILIATES),
            'role' => $role,
            'showInactive' => $showInactive,
            'users' => $users,
        ]);
    }

    public function showCreateUser()
    {
        $currentUserContext = $this->authorizeUserCreation();

        return view('user.form', $this->buildUserFormViewData(null, $currentUserContext));
    }

    public function storeUser(Request $request)
    {
        $currentUserContext = $this->authorizeUserCreation();

        $roleOptions = $this->getRoleOptionsForCurrentUser($currentUserContext);
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
        $ownerOptions = $this->getOwnerOptionsForCreate($targetRole, $currentUserContext);
        if (!$ownerOptions->pluck('idrep')->map(fn ($value) => (int) $value)->contains((int) $validated['referrer_repid'])) {
            return back()->withErrors(['referrer_repid' => 'Select a valid owner for the chosen account type.'])->withInput();
        }

        if ($request->boolean('enable_referral') && $targetRole !== Privilege::ROLE_AFFILIATE) {
            return back()->withErrors(['enable_referral' => 'Referral settings are only available for affiliate accounts.'])->withInput();
        }

        $selectedPermissions = $this->filterSelectedPermissions(
            $validated['permissions'] ?? [],
            $targetRole,
            $currentUserContext
        );

        $shouldRebuildTree = (int) $validated['status'] === 1;

        $permissionList = $this->buildPermissionListForRole($selectedPermissions, $targetRole);

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
            $currentUserContext->can(Permissions::EDIT_REFERRALS) &&
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
        $currentUserContext = $this->authorizeUserEdit($user);

        return view('user.form', $this->buildUserFormViewData($user, $currentUserContext));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::query()->with('role')->findOrFail($id);
        $currentUserContext = $this->authorizeUserEdit($user);

        $targetRole = $user->getRole();
        $canManageRole = $this->canManageUserRoles($user, $currentUserContext);
        $allowedRoleIds = $canManageRole
            ? array_keys($this->getRoleOptionsForCurrentUser($currentUserContext))
            : [$user->getRole()];

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:155',
            'last_name' => 'nullable|string|max:155',
            'email' => ['nullable', 'email', 'max:155', Rule::unique('rep', 'email')->ignore($user->idrep, 'idrep')],
            'cell_phone' => 'nullable|string|max:155',
            'company_name' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'skype' => 'nullable|string|max:255',
            'user_name' => [
                Rule::requiredIf($currentUserContext->type === Privilege::ROLE_GOD),
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

        if (in_array($currentUserContext->type, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true) && $user->getRole() !== Privilege::ROLE_GOD) {
            $ownerOptions = $this->getOwnerOptionsForEdit($targetRole, $currentUserContext);
            $requestedOwner = (int) ($validated['referrer_repid'] ?? $user->referrer_repid);
            if (!$ownerOptions->pluck('idrep')->map(fn ($value) => (int) $value)->contains($requestedOwner)) {
                return back()->withErrors(['referrer_repid' => 'Select a valid owner for the chosen account type.'])->withInput();
            }
        }

        $selectedPermissions = $this->filterSelectedPermissions(
            $validated['permissions'] ?? [],
            $targetRole,
            $currentUserContext
        );

        $requestedOwner = (int) ($validated['referrer_repid'] ?? $user->referrer_repid);
        $requestedStatus = (int) ($validated['status'] ?? $user->status);
        $shouldRebuildTree = $this->shouldRebuildUserTree($user, $requestedOwner, $requestedStatus);
        $shouldReassignBonuses = $this->shouldReassignInheritableBonuses($user, $requestedOwner);

        DB::transaction(function () use ($validated, $user, $targetRole, $canManageRole, $selectedPermissions, $shouldRebuildTree, $shouldReassignBonuses, $currentUserContext) {
            $updatePayload = [
                'first_name' => $validated['first_name'] ?? '',
                'last_name' => $validated['last_name'] ?? '',
                'cell_phone' => $validated['cell_phone'] ?? '',
                'email' => $validated['email'] ?? '',
                'status' => (int) $validated['status'],
                'skype' => $validated['telegram'] ?? $validated['skype'] ?? '',
                'company_name' => $validated['company_name'] ?? '',
            ];

            if ($currentUserContext->type === Privilege::ROLE_GOD) {
                $updatePayload['user_name'] = $validated['user_name'] ?: $user->user_name;
            }

            if (in_array($currentUserContext->type, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true) && $user->getRole() !== Privilege::ROLE_GOD) {
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
                $permissionList = $this->buildPermissionListForRole($selectedPermissions, $targetRole);

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
        $currentUserContext = CurrentUserSession::snapshot();

        $availableAffiliates = DB::table('rep')
            ->join('privileges', function ($join) {
                $join->on('privileges.rep_idrep', '=', 'rep.idrep')
                    ->where('privileges.is_rep', 1);
            })
            ->where('rep.lft', '>', $currentUserContext->data->lft)
            ->where('rep.rgt', '<', $currentUserContext->data->rgt)
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
        $currentUserContext = CurrentUserSession::snapshot();

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
            ->where('rep.lft', '>', $currentUserContext->data->lft)
            ->where('rep.rgt', '<', $currentUserContext->data->rgt)
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
        $currentUserContext = CurrentUserSession::snapshot();
        $user = $this->findPendingAffiliateOrFail($id);
        $assignableManagers = $this->getAssignableManagersForPendingAffiliate($currentUserContext);
        $hasReferralAccess = $currentUserContext->can(Permissions::EDIT_REFERRALS);
        $referralOptions = $hasReferralAccess
            ? User::query()->withRole(Privilege::ROLE_AFFILIATE)->myUsers()->orderBy('user_name')->get(['rep.idrep', 'rep.user_name'])
            : collect();

        return view('user.pending-activate', compact('user', 'assignableManagers', 'referralOptions', 'hasReferralAccess'));
    }

    public function activatePendingUser(Request $request, $id)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $user = $this->findPendingAffiliateOrFail($id);
        $assignableManagers = $this->getAssignableManagersForPendingAffiliate($currentUserContext);

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
            $currentUserContext->can(Permissions::EDIT_REFERRALS) &&
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

        return redirect('/user/pending')->with('message', BrandingLabels::affiliate() . ' activated successfully.');
    }

    public function viewBannedUsers()
    {
        $bounds = CurrentUserSession::data();

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

	public function getUserSubIds(Request $request, $id = null) {
        $affId = $id ?? $request->query('idrep');
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
		$payout = $request->filled('payout') ? $request->payout : null;

		if(CurrentUserSession::type() != Privilege::ROLE_AFFILIATE) {

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

		if(CurrentUserSession::type() != Privilege::ROLE_AFFILIATE) {

			if ($access) {
				DB::table('rep_has_offer')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'payout'        => $request->filled('payout') ? $request->payout : null
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
        $currentUserContext = CurrentUserSession::snapshot();

		$offers = DB::table('offer')
            ->where('status', '=', 1)
            ->select('idoffer', 'offer_name', 'payout', 'affiliate_payout')
            ->get()
            ->toArray();
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
				$offers[$index]->reppayout = null;
			}

            $offers[$index]->effective_payout = $offers[$index]->reppayout
                ?? $offers[$index]->affiliate_payout
                ?? $offers[$index]->payout;

            $offers[$index]->cap_enabled = $offerCap ? (bool) $offerCap->status : false;
            $offers[$index]->cap = $offerCap ? (int) $offerCap->cap : 0;
			$offers[$index]->idrep = $userID;
		}


		return view('user.offers')->with([
            'offers' => $offers,
            'name' => $userFName,
            'managedUser' => $user,
            'canEditAffiliatePayout' => $currentUserContext->can('edit_aff_payout'),
            'canManageOfferCaps' => $currentUserContext->type === Privilege::ROLE_GOD,
            'canManageOffers' => $currentUserContext->can(Permissions::EDIT_AFFILIATES) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canManageSubIds' => $currentUserContext->type === Privilege::ROLE_GOD && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canLoginAsUser' => $currentUserContext->type !== Privilege::ROLE_AFFILIATE && $user->idrep !== $currentUserContext->id,
        ]);
	}

	public function enableUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$status = $request->status;
		$message = "";

		if(CurrentUserSession::type() == Privilege::ROLE_GOD) {
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
		if(CurrentUserSession::type() == Privilege::ROLE_GOD) {
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

    private function authorizeUserCreation(): CurrentUserContext
    {
        $currentUserContext = CurrentUserSession::snapshot();

        if ($currentUserContext->type === Privilege::ROLE_AFFILIATE || $currentUserContext->type === Privilege::ROLE_UNKNOWN) {
            abort(403);
        }

        if (empty($this->getRoleOptionsForCurrentUser($currentUserContext))) {
            abort(403);
        }

        return $currentUserContext;
    }

    private function authorizeUserEdit(User $user): CurrentUserContext
    {
        $currentUserContext = CurrentUserSession::snapshot();
        $sessionUserId = $currentUserContext->id;
        $targetUserId = (int) $user->idrep;

        if ($currentUserContext->type === Privilege::ROLE_AFFILIATE) {
            abort_unless($targetUserId === $sessionUserId, 403);
            return $currentUserContext;
        }

        if ($targetUserId !== $sessionUserId && !$currentUserContext->can(Permissions::EDIT_AFFILIATES)) {
            abort(403);
        }

        if ($currentUserContext->type === Privilege::ROLE_MANAGER && $targetUserId !== $sessionUserId && !LegacyUser::userOwnsUser($sessionUserId, $targetUserId)) {
            abort(403);
        }

        return $currentUserContext;
    }

    private function authorizeReferralEdit(User $user)
    {
        abort_unless(CurrentUserSession::permissions()->can(Permissions::EDIT_REFERRALS), 403);
        abort_unless(LegacyUser::hasAffiliate($user->idrep), 403);
    }

    private function buildUserFormViewData(?User $user = null, ?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $isEdit = $user !== null;
        $canManageRoles = $isEdit ? $this->canManageUserRoles($user, $currentUserContext) : true;
        $roleOptions = $this->getRoleOptionsForCurrentUser($currentUserContext);
        $selectedRole = (int) old('priv', $isEdit ? $user->getRole() : (array_key_first($roleOptions) ?? Privilege::ROLE_AFFILIATE));
        $selectedPermissions = $isEdit ? $this->getSelectedPermissionsForUser($user->idrep) : old('permissions', []);
        $permissionOptionsByRole = [];
        if (!$isEdit || $canManageRoles) {
            foreach (array_keys($roleOptions) as $roleId) {
                $permissionOptionsByRole[$roleId] = $this->getPermissionOptionsForRole((int) $roleId, $currentUserContext);
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
            'ownerOptionsByRole' => $this->getOwnerOptionsByRoleForView($isEdit, $currentUserContext),
            'permissionOptionsByRole' => $permissionOptionsByRole,
            'selectedPermissions' => $selectedPermissions,
            'canManageRoles' => $canManageRoles,
            'canEditUsername' => !$isEdit || $currentUserContext->type === Privilege::ROLE_GOD,
            'canEditOwner' => !$isEdit || in_array($currentUserContext->type, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true),
            'canLoginAsUser' => $isEdit && $currentUserContext->type !== Privilege::ROLE_AFFILIATE && $user->idrep !== $currentUserContext->id,
            'canManageOffers' => $isEdit && $currentUserContext->can(Permissions::EDIT_AFFILIATES) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canManageSubIds' => $isEdit && $currentUserContext->type === Privilege::ROLE_GOD && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'canCreateReferrals' => !$isEdit && $currentUserContext->can(Permissions::EDIT_REFERRALS),
            'canEditReferrals' => $isEdit && $currentUserContext->can(Permissions::EDIT_REFERRALS) && $user->getRole() === Privilege::ROLE_AFFILIATE,
            'referralOptions' => $currentUserContext->can(Permissions::EDIT_REFERRALS)
                ? User::query()->withRole(Privilege::ROLE_AFFILIATE)->myUsers()->orderBy('rep.user_name')->get(['rep.idrep', 'rep.user_name'])
                : collect(),
            'currentReferralUserId' => $currentReferralUserId,
            'hasChildren' => $isEdit ? $this->userHasChildren($user) : false,
            'hasReferralStructure' => $isEdit ? $this->userHasReferralStructure($user) : false,
            'statsOwnerLabel' => $isEdit && $user->referrer ? $user->referrer->user_name : 'Choose on save',
        ];
    }

    private function getRoleOptionsForCurrentUser(?CurrentUserContext $currentUserContext = null): array
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $options = [];

        if ($currentUserContext->type === Privilege::ROLE_GOD || $currentUserContext->can(Permissions::CREATE_ADMINS)) {
            $options[Privilege::ROLE_ADMIN] = 'Admin';
        }

        if ($currentUserContext->type === Privilege::ROLE_GOD || $currentUserContext->can(Permissions::CREATE_MANAGERS)) {
            $options[Privilege::ROLE_MANAGER] = BrandingLabels::account();
        }

        if ($currentUserContext->type === Privilege::ROLE_GOD || $currentUserContext->can(Permissions::CREATE_AFFILIATES)) {
            $options[Privilege::ROLE_AFFILIATE] = BrandingLabels::affiliate();
        }

        return $options;
    }

    private function getOwnerOptionsByRoleForView(bool $isEdit, ?CurrentUserContext $currentUserContext = null): array
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $byRole = [];
        foreach (array_keys($this->getRoleOptionsForCurrentUser($currentUserContext)) as $roleId) {
            $byRole[$roleId] = ($isEdit
                ? $this->getOwnerOptionsForEdit((int) $roleId, $currentUserContext)
                : $this->getOwnerOptionsForCreate((int) $roleId, $currentUserContext))
                ->map(fn ($owner) => ['idrep' => (int) $owner->idrep, 'user_name' => $owner->user_name])
                ->values()
                ->all();
        }

        return $byRole;
    }

    private function getOwnerOptionsForCreate(int $targetRole, ?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        return match ($targetRole) {
            Privilege::ROLE_ADMIN => $this->getGodOwnersForCreate(),
            Privilege::ROLE_MANAGER => $this->getAdminOwnersForCreate($currentUserContext),
            Privilege::ROLE_AFFILIATE => $this->getManagerOwnersForCreate($currentUserContext),
            default => collect(),
        };
    }

    private function getOwnerOptionsForEdit(int $targetRole, ?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        if (!in_array($currentUserContext->type, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true)) {
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

    private function getAdminOwnersForCreate(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        if ($currentUserContext->type === Privilege::ROLE_GOD) {
            return $this->getOwnersByPrivilegeColumn('is_admin');
        }

        if ($currentUserContext->type === Privilege::ROLE_ADMIN) {
            return collect([(object) ['idrep' => $currentUserContext->id, 'user_name' => $currentUserContext->data->user_name]]);
        }

        $parentAdmin = User::query()->find($currentUserContext->data->referrer_repid);
        return $parentAdmin ? collect([(object) ['idrep' => $parentAdmin->idrep, 'user_name' => $parentAdmin->user_name]]) : collect();
    }

    private function getManagerOwnersForCreate(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        if ($currentUserContext->type === Privilege::ROLE_GOD) {
            return $this->getOwnersByPrivilegeColumn('is_manager');
        }

        if ($currentUserContext->type === Privilege::ROLE_ADMIN) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->myUsers()
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        return collect([(object) ['idrep' => $currentUserContext->id, 'user_name' => $currentUserContext->data->user_name]]);
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

    private function getPermissionOptionsForRole(int $role, ?CurrentUserContext $currentUserContext = null): array
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $sessionPermissions = $currentUserContext->permissions;
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
                if (!in_array($currentUserContext->type, $details['allowed_user_types'], true)) {
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

    private function filterSelectedPermissions(
        array $selectedPermissions,
        int $role,
        ?CurrentUserContext $currentUserContext = null
    ): array
    {
        $allowed = array_keys($this->getPermissionOptionsForRole($role, $currentUserContext));

        return array_values(array_intersect($selectedPermissions, $allowed));
    }

    private function buildPermissionListForRole(array $selectedPermissions, int $role): array
    {
        $permissionList = Permissions::defaultUserPermissions([], $role);

        foreach ($selectedPermissions as $permissionKey) {
            $permissionList[$permissionKey] = 1;
        }

        if ($role === Privilege::ROLE_AFFILIATE) {
            $permissionList[Permissions::EDIT_AFFILIATES] = 1;
        }

        return $permissionList;
    }

    private function canManageUserRoles(User $user, ?CurrentUserContext $currentUserContext = null): bool
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        return in_array($currentUserContext->type, [Privilege::ROLE_GOD, Privilege::ROLE_ADMIN], true)
            && $user->getRole() !== Privilege::ROLE_GOD
            && !empty($this->getRoleOptionsForCurrentUser($currentUserContext));
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

        if (!LegacyUser::userOwnsUser(CurrentUserSession::id(), $user->idrep)) {
            abort(403);
        }

        return $user;
    }

    private function getAssignableManagersForPendingAffiliate(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        if ($currentUserContext->type === Privilege::ROLE_GOD) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        if ($currentUserContext->type === Privilege::ROLE_ADMIN) {
            return User::query()
                ->withRole(Privilege::ROLE_MANAGER)
                ->myUsers()
                ->where('rep.status', 1)
                ->orderBy('rep.user_name')
                ->get(['rep.idrep', 'rep.user_name']);
        }

        return collect([
            (object) [
                'idrep' => $currentUserContext->id,
                'user_name' => $currentUserContext->data->user_name,
            ],
        ]);
    }
}
