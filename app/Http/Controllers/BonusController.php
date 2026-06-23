<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\LegacyPermissions as Permissions;
use App\Support\LegacyUser;

class BonusController extends Controller
{
    public function index()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($this->canManageBonuses($currentUserContext), 403);

        $bonuses = collect((new LegacyBonus($currentUserContext->id, true))->bonuses);

        return view('bonus.index', [
            'bonuses' => $bonuses,
            'canCreateBonuses' => $currentUserContext->can(Permissions::CREATE_BONUSES),
            'canAssignBonuses' => $currentUserContext->can(Permissions::ASSIGN_BONUSES),
            'canProcessBonuses' => $currentUserContext->type === Privilege::ROLE_GOD,
        ]);
    }

    public function create()
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::CREATE_BONUSES), 403);

        return view('bonus.form', [
            'mode' => 'create',
            'bonus' => null,
            'action' => '/bonuses/create',
            'userGroups' => $this->userGroupsForBonus(null, $currentUserContext),
        ]);
    }

    public function store(Request $request)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::CREATE_BONUSES), 403);

        $payload = $this->validateBonus($request);

        $bonusId = LegacyBonus::createBonus(
            $payload['name'],
            $payload['sales_required'],
            $payload['payout'],
            $payload['status'],
            $request->boolean('inheritable') ? 1 : 0
        );

        if ($bonusId && ($request->filled('user_ids') || $request->filled('replist'))) {
            $this->syncBonusUsers(
                (int) $bonusId,
                $request->input('user_ids', $request->input('replist', [])),
                $currentUserContext
            );
        }

        return redirect("/bonuses/{$bonusId}/edit")->with('message', 'Bonus created successfully.');
    }

    public function edit($bonus)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::CREATE_BONUSES), 403);

        $bonusRecord = $this->findBonusOrFail((int) $bonus);

        return view('bonus.form', [
            'mode' => 'edit',
            'bonus' => $bonusRecord,
            'action' => "/bonuses/{$bonusRecord->id}/edit",
            'userGroups' => $this->userGroupsForBonus((int) $bonusRecord->id, $currentUserContext),
        ]);
    }

    public function update(Request $request, $bonus)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::CREATE_BONUSES), 403);

        $bonusRecord = $this->findBonusOrFail((int) $bonus);
        $payload = $this->validateBonus($request);

        LegacyBonus::updateBonus(
            (int) $bonusRecord->id,
            $payload['name'],
            $payload['sales_required'],
            $payload['payout'],
            $payload['status'],
            $request->boolean('inheritable') ? 1 : 0
        );

        $this->syncBonusUsers(
            (int) $bonusRecord->id,
            $request->input('user_ids', $request->input('replist', [])),
            $currentUserContext
        );

        return redirect("/bonuses/{$bonusRecord->id}/edit")->with('message', 'Bonus updated successfully.');
    }

    public function assign($bonus)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::ASSIGN_BONUSES), 403);

        $bonusRecord = $this->findBonusOrFail((int) $bonus);

        return view('bonus.assign', [
            'bonus' => $bonusRecord,
            'userGroups' => $this->userGroupsForBonus((int) $bonusRecord->id, $currentUserContext),
        ]);
    }

    public function updateAssignment(Request $request, $bonus)
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->can(Permissions::ASSIGN_BONUSES), 403);

        $bonusRecord = $this->findBonusOrFail((int) $bonus);

        $this->syncBonusUsers(
            (int) $bonusRecord->id,
            $request->input('user_ids', $request->input('replist', [])),
            $currentUserContext
        );

        return redirect("/bonuses/{$bonusRecord->id}/assign")->with('message', 'Bonus assignment updated successfully.');
    }

    public function process()
    {
        abort_unless(CurrentUserSession::type() === Privilege::ROLE_GOD, 403);

        $affiliates = LegacyUser::selectAllAffiliateIDs()->fetchAll(\PDO::FETCH_OBJ);

        foreach ($affiliates as $affiliate) {
            (new LegacyBonus($affiliate->idrep))->processAll();
        }

        return redirect('/bonuses')->with('message', 'Bonus processing completed.');
    }

    private function validateBonus(Request $request): array
    {
        if ($request->has('salesRequired') && !$request->has('sales_required')) {
            $request->merge(['sales_required' => $request->input('salesRequired')]);
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'sales_required' => 'required|integer|min:0',
            'payout' => 'required|numeric|min:0',
            'status' => 'required|boolean',
        ]);
    }

    private function findBonusOrFail(int $bonusId): \stdClass
    {
        $bonus = LegacyBonus::querySelectOne($bonusId)->fetch(\PDO::FETCH_OBJ);

        abort_if(!$bonus, 404);

        return $bonus;
    }

    private function userGroupsForBonus(
        ?int $bonusId = null,
        ?CurrentUserContext $currentUserContext = null
    ): array
    {
        $currentUserContext ??= CurrentUserSession::snapshot();
        $assignedUserIds = $bonusId
            ? DB::table('user_has_bonus')->where('bonus_id', '=', $bonusId)->pluck('user_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $groups = [];

        if ($currentUserContext->can(Permissions::CREATE_ADMINS)) {
            $groups[] = $this->userGroup('Admins', LegacyUser::selectAdmins()->fetchAll(\PDO::FETCH_ASSOC), $assignedUserIds);
        }

        if ($currentUserContext->can(Permissions::CREATE_MANAGERS)) {
            $groups[] = $this->userGroup('Managers', LegacyUser::selectOwnedManagers()->fetchAll(\PDO::FETCH_ASSOC), $assignedUserIds);
        }

        if ($currentUserContext->can(Permissions::CREATE_AFFILIATES)) {
            $groups[] = $this->userGroup('Affiliates', LegacyUser::selectAllOwnedAffiliates()->fetchAll(\PDO::FETCH_ASSOC), $assignedUserIds);
        }

        return $groups;
    }

    private function userGroup(string $name, array $users, array $assignedUserIds): array
    {
        return [
            'name' => $name,
            'users' => collect($users)
                ->map(function (array $user) use ($assignedUserIds) {
                    $user['idrep'] = (int) $user['idrep'];
                    $user['assigned'] = in_array($user['idrep'], $assignedUserIds, true);

                    return (object) $user;
                })
                ->values(),
        ];
    }

    private function syncBonusUsers(
        int $bonusId,
        array $selectedUserIds,
        ?CurrentUserContext $currentUserContext = null
    ): void
    {
        $visibleUserIds = collect($this->userGroupsForBonus($bonusId, $currentUserContext))
            ->flatMap(fn ($group) => $group['users'])
            ->pluck('idrep')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $selectedUserIds = collect($selectedUserIds)
            ->map(fn ($id) => (int) $id)
            ->intersect($visibleUserIds)
            ->values()
            ->all();
        $currentlyAssigned = DB::table('user_has_bonus')
            ->where('bonus_id', '=', $bonusId)
            ->whereIn('user_id', $visibleUserIds ?: [0])
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $toAssign = array_values(array_diff($selectedUserIds, $currentlyAssigned));
        $toRemove = array_values(array_diff($currentlyAssigned, $selectedUserIds));

        if (!empty($toAssign)) {
            LegacyBonus::assignUsersToBonus($bonusId, $toAssign);
        }

        if (!empty($toRemove)) {
            LegacyBonus::removeUsersFromBonus($bonusId, $toRemove);
        }
    }

    private function canManageBonuses(?CurrentUserContext $currentUserContext = null): bool
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        return $currentUserContext->can(Permissions::ASSIGN_BONUSES)
            || $currentUserContext->can(Permissions::CREATE_BONUSES);
    }
}
