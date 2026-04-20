@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', $pageTitle)

@section('content')
    @php
        $isEdit = $mode === 'edit';
        $targetUser = $managedUser;
        $currentRole = $isEdit ? $targetUser->getRole() : null;
        $roleLabels = [
            \App\Privilege::ROLE_GOD => 'God',
            \App\Privilege::ROLE_ADMIN => 'Admin',
            \App\Privilege::ROLE_MANAGER => $accountTypeLabel,
            \App\Privilege::ROLE_AFFILIATE => $affiliateTypeLabel,
        ];
        $selectedRoleLabel = $roleLabels[$isEdit ? $currentRole : $selectedRole] ?? 'User';
        $statusLabel = $isEdit ? ((int) $targetUser->status === 1 ? 'Active' : 'Disabled') : 'Ready';
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageTitle }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $isEdit
                            ? 'Update account details, assignment, permissions, and workflow shortcuts from one page in the new shell.'
                            : 'Create a new account with the right role, owner, and permissions without dropping back into the legacy editor.' }}
                    </p>
                </div>

                @include('user.partials.account-actions', [
                    'managedUser' => $targetUser,
                    'canManageOffers' => $isEdit && $canManageOffers,
                    'canManageSubIds' => $isEdit && $canManageSubIds,
                    'canLoginAsUser' => $isEdit && $canLoginAsUser,
                    'currentWorkspace' => 'edit',
                ])
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Mode</p>
                <p class="bp-stat-value">{{ $isEdit ? 'Edit' : 'Create' }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Updating an existing account.' : 'Creating a brand new account.' }}</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Role</p>
                <p class="bp-stat-value">{{ $selectedRoleLabel }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Current or selected account type.' : 'Chosen account type for this new user.' }}</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Owner</p>
                <p class="bp-stat-value">{{ $statsOwnerLabel }}</p>
                <p class="bp-stat-note">The assigned upstream owner for this account.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ $statusLabel }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Current saved account state.' : 'Status will be set when the account is saved.' }}</p>
            </article>
        </section>

        @if($isEdit && ($hasChildren || $hasReferralStructure))
            <div class="bp-inline-note">
                <strong>Role changes may be limited</strong>
                <span>
                    @if($hasChildren)
                        This account has users assigned beneath it, so it cannot be downgraded until that tree is cleared.
                    @elseif($hasReferralStructure)
                        This account has referral structures attached, so it cannot be upgraded until those are removed.
                    @endif
                </span>
            </div>
        @endif

        <form action="{{ $formAction }}" method="post" class="space-y-6 lg:space-y-8">
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">User Details</p>
                        <h3 class="bp-section-title value_span9">Profile and contact info</h3>
                    </div>
                    <p class="bp-table-meta">These fields cover the core account identity and contact details.</p>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">First Name</span>
                        <input class="bp-form-input" type="text" name="first_name" maxlength="155" value="{{ old('first_name', $targetUser->first_name ?? '') }}">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Last Name</span>
                        <input class="bp-form-input" type="text" name="last_name" maxlength="155" value="{{ old('last_name', $targetUser->last_name ?? '') }}">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Email</span>
                        <input class="bp-form-input" type="email" name="email" maxlength="155" value="{{ old('email', $targetUser->email ?? '') }}">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Phone</span>
                        <input class="bp-form-input" type="text" name="cell_phone" maxlength="155" value="{{ old('cell_phone', $targetUser->cell_phone ?? '') }}">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Company</span>
                        <input class="bp-form-input" type="text" name="company_name" maxlength="255" value="{{ old('company_name', $targetUser->company_name ?? '') }}">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Skype</span>
                        <input class="bp-form-input" type="text" name="skype" maxlength="255" value="{{ old('skype', $targetUser->skype ?? '') }}">
                    </label>
                </div>
            </section>

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Access Settings</p>
                        <h3 class="bp-section-title value_span9">Account credentials and ownership</h3>
                    </div>
                    <p class="bp-table-meta">Role and owner controls stay aligned with the legacy permission rules behind the scenes.</p>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Username</span>
                        <input
                            class="bp-form-input"
                            type="text"
                            name="user_name"
                            maxlength="155"
                            value="{{ old('user_name', $targetUser->user_name ?? '') }}"
                            {{ $canEditUsername ? '' : 'readonly' }}
                        >
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" name="status">
                            <option value="1" {{ (int) old('status', $targetUser->status ?? 1) === 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (int) old('status', $targetUser->status ?? 1) === 0 ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">{{ $isEdit ? 'New Password' : 'Password' }}</span>
                        <input class="bp-form-input" type="password" name="password" minlength="5" maxlength="255">
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">{{ $isEdit ? 'Confirm New Password' : 'Confirm Password' }}</span>
                        <input class="bp-form-input" type="password" name="confirmpassword" minlength="5" maxlength="255">
                    </label>

                    @if($canManageRoles)
                        <label class="bp-form-field">
                            <span class="bp-form-label">Account Type</span>
                            <select class="bp-form-input" id="priv" name="priv">
                                @foreach($roleOptions as $roleId => $label)
                                    <option value="{{ $roleId }}" {{ (int) old('priv', $selectedRole) === (int) $roleId ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    @else
                        <input type="hidden" id="priv" name="priv" value="{{ $selectedRole }}">
                    @endif

                    @if($canEditOwner)
                        <label class="bp-form-field">
                            <span class="bp-form-label">Owner</span>
                            <select class="bp-form-input" id="referrer_repid" name="referrer_repid"></select>
                        </label>
                    @endif
                </div>
            </section>

            @if(!empty($permissionOptionsByRole))
                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Permissions</p>
                            <h3 class="bp-section-title value_span9">Enabled account capabilities</h3>
                        </div>
                        <p class="bp-table-meta">Only permissions you can actually grant are shown for the selected role.</p>
                    </div>

                    <div class="bp-permission-grid mt-6" id="permissionsP">
                        @foreach($permissionOptionsByRole as $roleId => $permissions)
                            @foreach($permissions as $permissionKey => $description)
                                <label class="bp-permission-item" data-role="{{ $roleId }}">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permissionKey }}"
                                        {{ in_array($permissionKey, old('permissions', $selectedPermissions), true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $description }}</span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!$isEdit && \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_REFERRALS))
                <section class="bp-card value_span8" id="create_referral_panel">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Referral Setup</p>
                            <h3 class="bp-section-title value_span9">Optional affiliate referral</h3>
                        </div>
                        <p class="bp-table-meta">This only applies when the selected role is {{ strtolower($affiliateTypeLabel) }}.</p>
                    </div>

                    <div class="mt-6 space-y-5">
                        <label class="bp-choice-pill">
                            <input type="checkbox" id="enable_referral" name="enable_referral" value="1" {{ old('enable_referral') ? 'checked' : '' }}>
                            <span>Enable referral on creation</span>
                        </label>

                        <div class="bp-form-grid md:grid-cols-2" id="referral_fields">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Referrer</span>
                                <select class="bp-form-input" id="referral_user_id" name="referral_user_id">
                                    <option value="">Select affiliate</option>
                                    @foreach($referralOptions as $referralUser)
                                        <option value="{{ $referralUser->idrep }}" {{ (int) old('referral_user_id') === (int) $referralUser->idrep ? 'selected' : '' }}>{{ $referralUser->user_name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Type</span>
                                <select class="bp-form-input" id="referral_type" name="referral_type">
                                    <option value="flat" {{ old('referral_type') === 'flat' ? 'selected' : '' }}>Flat Fee</option>
                                    <option value="percentage" {{ old('referral_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Start Date</span>
                                <input class="bp-form-input" id="start_date" name="start_date" type="text" value="{{ old('start_date') }}">
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">End Date</span>
                                <input class="bp-form-input" id="end_date" name="end_date" type="text" value="{{ old('end_date') }}">
                            </label>

                            <label class="bp-form-field bp-form-field-full">
                                <span class="bp-form-label">Amount</span>
                                <input class="bp-form-input" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', 0) }}">
                            </label>
                        </div>
                    </div>
                </section>
            @endif

            @if($isEdit && $canEditReferrals)
                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Referral Settings</p>
                            <h3 class="bp-section-title value_span9">Current referral structure</h3>
                        </div>
                        <a href="/user/{{ $targetUser->idrep }}/referrals" class="bp-button-secondary">Open detailed referral editor</a>
                    </div>

                    <div class="mt-6">
                        @if($currentReferralUserId)
                            <div class="bp-form-grid md:grid-cols-2">
                                <label class="bp-form-field">
                                    <span class="bp-form-label">Current Referrer</span>
                                    <select class="bp-form-input" name="referrer_box">
                                        @foreach($referralOptions as $referralUser)
                                            <option value="{{ $referralUser->idrep }}" {{ (int) old('referrer_box', $currentReferralUserId) === (int) $referralUser->idrep ? 'selected' : '' }}>{{ $referralUser->user_name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        @else
                            <div class="bp-inline-note">
                                <strong>No referral structure</strong>
                                <span>This affiliate does not currently have a referral record attached.</span>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            @if($isEdit && $canManageSubIds)
                <section class="bp-card value_span8" id="sub-id-tools">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Sub ID Controls</p>
                            <h3 class="bp-section-title value_span9">Blocked sub IDs</h3>
                        </div>
                        <p class="bp-table-meta">Search existing sub IDs, block new ones, and unblock entries without leaving the account editor.</p>
                    </div>

                    <div id="subid_status" class="bp-error-banner hidden mt-4">
                        <p></p>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                        <div class="rounded-3xl border border-slate-200/70 bg-white/70 p-4 shadow-sm">
                            <p class="bp-detail-label">Workflow</p>
                            <p class="mt-2 text-sm leading-7 text-slate-500">
                                The buttons below use the existing block and unblock endpoints, so this panel stays aligned with the legacy moderation logic while living in the new shell.
                            </p>
                        </div>

                        <div class="bp-offer-search">
                            <label class="bp-detail-label" for="subIdSearch">Search sub IDs</label>
                            <input
                                id="subIdSearch"
                                class="bp-search-input"
                                type="text"
                                placeholder="Search by sub ID value"
                            >
                        </div>
                    </div>

                    <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                        <table class="table table-striped table_01 large_table" id="subIdTable">
                            <thead>
                            <tr>
                                <th class="value_span9">Sub ID</th>
                                <th class="value_span9">Status</th>
                                <th class="value_span9">Actions</th>
                            </tr>
                            </thead>
                            <tbody id="subid_content">
                            <tr>
                                <td colspan="3">
                                    <span class="bp-table-empty">Loading sub IDs...</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">
                    {{ $isEdit ? 'Save user' : 'Create user' }}
                </button>
                <a href="/user/manage" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        (() => {
            const ownerOptionsByRole = @json($ownerOptionsByRole);
            const roleSelect = document.getElementById('priv');
            const ownerSelect = document.getElementById('referrer_repid');
            const permissionItems = Array.from(document.querySelectorAll('[data-role]'));
            const referralPanel = document.getElementById('create_referral_panel');
            const referralToggle = document.getElementById('enable_referral');
            const referralFields = document.getElementById('referral_fields');
            const selectedOwner = @json((int) old('referrer_repid', $managedUser->referrer_repid ?? 0));
            const subIdSearch = document.getElementById('subIdSearch');
            const subIdContainer = document.getElementById('subid_content');
            const subIdStatus = document.getElementById('subid_status');
            const subIdTable = $('#subIdTable');
            const managedUserId = @json((int) ($managedUser->idrep ?? 0));
            let subIds = [];

            const refreshOwners = () => {
                if (!roleSelect || !ownerSelect) {
                    return;
                }

                const role = String(roleSelect.value);
                const options = ownerOptionsByRole[role] || [];

                ownerSelect.innerHTML = '';
                options.forEach((owner, index) => {
                    const option = new Option(owner.user_name, owner.idrep, false, false);
                    if ((selectedOwner && Number(selectedOwner) === Number(owner.idrep)) || (!selectedOwner && index === 0)) {
                        option.selected = true;
                    }
                    ownerSelect.append(option);
                });
            };

            const refreshPermissions = () => {
                if (!roleSelect || !permissionItems.length) {
                    return;
                }

                const role = String(roleSelect.value);
                permissionItems.forEach((item) => {
                    const visible = item.getAttribute('data-role') === role;
                    item.style.display = visible ? '' : 'none';
                    item.querySelector('input').disabled = !visible;
                    if (!visible) {
                        item.querySelector('input').checked = false;
                    }
                });
            };

            const refreshReferralPanel = () => {
                if (!roleSelect || !referralPanel) {
                    return;
                }

                const isAffiliate = Number(roleSelect.value) === {{ \App\Privilege::ROLE_AFFILIATE }};
                referralPanel.style.display = isAffiliate ? '' : 'none';

                if (!isAffiliate && referralToggle) {
                    referralToggle.checked = false;
                }

                if (referralFields && referralToggle) {
                    const enabled = isAffiliate && referralToggle.checked;
                    referralFields.style.display = enabled ? '' : 'none';
                    referralFields.querySelectorAll('input, select').forEach((field) => {
                        field.disabled = !enabled;
                    });
                }
            };

            if (roleSelect) {
                roleSelect.addEventListener('change', () => {
                    refreshOwners();
                    refreshPermissions();
                    refreshReferralPanel();
                });
            }

            if (referralToggle) {
                referralToggle.addEventListener('change', refreshReferralPanel);
            }

            $("#start_date, #end_date").datepicker({dateFormat: 'yy-mm-dd'});

            const setSubIdMessage = (message = '', isError = true) => {
                if (!subIdStatus) {
                    return;
                }

                subIdStatus.classList.toggle('hidden', !message);
                subIdStatus.classList.toggle('bp-subid-success', !isError && !!message);
                const textNode = subIdStatus.querySelector('p');
                if (textNode) {
                    textNode.textContent = message;
                }
            };

            const renderSubIds = (rows) => {
                if (!subIdContainer) {
                    return;
                }

                if (!rows.length) {
                    subIdContainer.innerHTML = `
                        <tr>
                            <td colspan="3"><span class="bp-table-empty">No sub IDs matched this search.</span></td>
                        </tr>
                    `;
                    if (subIdTable.length) {
                        subIdTable.trigger('update');
                    }
                    return;
                }

                subIdContainer.innerHTML = rows.map((subId) => {
                    const isBlocked = Boolean(subId.blocked);
                    return `
                        <tr data-search="${String(subId.subId || '').toLowerCase()}">
                            <td>${subId.subId}</td>
                            <td>
                                <span class="bp-status-pill ${isBlocked ? 'bp-status-pill-active' : ''}">
                                    ${isBlocked ? 'Blocked' : 'Open'}
                                </span>
                            </td>
                            <td class="actions">
                                <div class="bp-table-actions bp-subid-actions">
                                    <button
                                        type="button"
                                        class="btn btn-default btn-sm ${isBlocked ? '' : 'value_span6-2 value_span2 value_span1-2'} js-block-subid"
                                        data-subid="${subId.subId}"
                                        ${isBlocked ? 'disabled' : ''}>
                                        ${isBlocked ? 'Blocked' : 'Block ID'}
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-default btn-sm value_span6-1 value_span4 js-unblock-subid"
                                        data-subid="${subId.subId}"
                                        ${isBlocked ? '' : 'disabled'}>
                                        Unblock
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');

                if (subIdTable.length) {
                    subIdTable.trigger('update');
                }
            };

            const filterSubIds = () => {
                if (!subIdSearch) {
                    renderSubIds(subIds);
                    return;
                }

                const query = subIdSearch.value.trim().toLowerCase();
                const filtered = subIds.filter((subId) => String(subId.subId || '').toLowerCase().includes(query));
                renderSubIds(filtered);
            };

            const updateSubId = async (endpoint, subId, blocked) => {
                setSubIdMessage('');

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        user_id: managedUserId,
                        sub_id: subId
                    }),
                    credentials: 'same-origin'
                });

                const payload = await response.json();
                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Unable to update this sub ID right now.');
                }

                subIds = subIds.map((row) => {
                    if (String(row.subId) !== String(subId)) {
                        return row;
                    }

                    return {
                        ...row,
                        blocked
                    };
                });

                filterSubIds();
                setSubIdMessage(blocked ? 'Sub ID blocked successfully.' : 'Sub ID unblocked successfully.', false);
            };

            if (subIdContainer) {
                subIdContainer.addEventListener('click', async (event) => {
                    const blockButton = event.target.closest('.js-block-subid');
                    const unblockButton = event.target.closest('.js-unblock-subid');

                    try {
                        if (blockButton) {
                            await updateSubId('/user/block-sub-id', blockButton.dataset.subid, true);
                        }

                        if (unblockButton) {
                            await updateSubId('/user/unblock-sub-id', unblockButton.dataset.subid, false);
                        }
                    } catch (error) {
                        setSubIdMessage(error.message || 'Unable to update this sub ID right now.');
                    }
                });
            }

            if (subIdSearch) {
                subIdSearch.addEventListener('input', filterSubIds);
            }

            if (subIdContainer) {
                fetch(`/user/${managedUserId}/sub-ids`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then((response) => response.json())
                    .then((payload) => {
                        subIds = Array.isArray(payload) ? payload : [];
                        filterSubIds();

                        if (subIdTable.length) {
                            subIdTable.tablesorter({
                                sortList: [[0, 0]],
                                widgets: ['staticRow']
                            });
                        }
                    })
                    .catch(() => {
                        subIdContainer.innerHTML = `
                            <tr>
                                <td colspan="3"><span class="bp-table-empty">Unable to load sub IDs right now.</span></td>
                            </tr>
                        `;
                        setSubIdMessage('Unable to load sub IDs right now.');
                    });
            }

            refreshOwners();
            refreshPermissions();
            refreshReferralPanel();
        })();
    </script>
@endsection
