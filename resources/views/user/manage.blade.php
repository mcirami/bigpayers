@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Users')

@section('content')
    @php
        $role = (int) request('role', 3);
        $showInactive = (int) request('showInactive', 0) === 1;
        $roleLabels = [
            \App\Privilege::ROLE_GOD => 'God',
            \App\Privilege::ROLE_ADMIN => 'Admins',
            \App\Privilege::ROLE_MANAGER => env('ACCOUNT_TYPE_TEXT') . 's',
            \App\Privilege::ROLE_AFFILIATE => 'Agents',
        ];
        $selectedRoleLabel = $roleLabels[$role] ?? 'Users';
        $totalUsers = count($users);
        $usersWithManagers = $users->filter(fn ($user) => !empty(optional($user->referrer)->user_name))->count();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">User account directory</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review account access, jump into edits, and log into downstream users without leaving the new shell.
                    </p>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    {{ $selectedRoleLabel }} • {{ $showInactive ? 'Inactive only' : 'Active only' }}
                </div>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="bp-report-toolbar">
                    @include('report.options.user-type')
                    @include('report.options.active')
                </div>

                <div class="bp-offer-search">
                    <label class="bp-detail-label" for="searchBox">Search users</label>
                    <input
                        id="searchBox"
                        class="bp-search-input"
                        type="text"
                        placeholder="Search by username, email, or ID"
                    >
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visible Accounts</p>
                <p class="bp-stat-value">{{ $totalUsers }}</p>
                <p class="bp-stat-note">Current {{ strtolower($selectedRoleLabel) }} loaded into the searchable directory.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Relationship Map</p>
                <p class="bp-stat-value">{{ $usersWithManagers }}</p>
                <p class="bp-stat-note">Users in this list that already resolve to a parent manager or admin.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Listing Scope</p>
                <p class="bp-stat-value">{{ $showInactive ? 'Inactive' : 'Active' }}</p>
                <p class="bp-stat-note">Toggle between active and inactive accounts without leaving the page.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Role Filter</p>
                <p class="bp-stat-value">{{ $selectedRoleLabel }}</p>
                <p class="bp-stat-note">The current role view controls which account actions appear below.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Directory</p>
                    <h3 class="bp-section-title value_span9">Searchable user table</h3>
                </div>
                <p class="bp-table-meta">Sorting is still powered by the existing tablesorter scripts while the layout is upgraded.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-striped table_01 manage_user_table" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">User</th>
                        <th class="value_span9">Email</th>
                        <th class="value_span9">Manager</th>
                        <th class="value_span9">Added</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="users_container"></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            const canEditAffiliates = @json(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_AFFILIATES));
            const canCreateAffiliates = @json(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_AFFILIATES));
            const canCreateManagers = @json(\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::CREATE_MANAGERS));
            const role = @json($role);
            const users = @json($users);
            const itemsContainer = document.querySelector("#users_container");
            const searchBox = document.getElementById("searchBox");
            const table = $("#mainTable");

            const escapeHtml = (value) => {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };

            const renderActions = (user) => {
                const actions = [];

                if (canEditAffiliates) {
                    actions.push(
                        `<a class="btn btn-default btn-sm value_span6-1 value_span4" href="/aff_update.php?idrep=${user.idrep}">Edit</a>`
                    );
                }

                if (canCreateAffiliates) {
                    actions.push(
                        `<a class="btn btn-default btn-sm value_span5-1" href="#" onclick="adminLogin(${user.idrep}); return false;">Login</a>`
                    );
                }

                if (canCreateManagers && Number(role) === {{ \App\Privilege::ROLE_MANAGER }}) {
                    actions.push(
                        `<a class="btn btn-default btn-sm value_span6-1 value_span4" href="/user/${user.idrep}/affiliates">View Agents</a>`
                    );
                }

                if (!actions.length) {
                    actions.push('<span class="bp-table-empty">No actions</span>');
                }

                return `<div class="bp-table-actions">${actions.join('')}</div>`;
            };

            const showUsers = (userRows) => {
                const html = userRows.map((user) => {
                    const managerName = user.referrer && user.referrer.user_name ? user.referrer.user_name : 'No manager assigned';

                    return `
                        <tr>
                            <td>${escapeHtml(user.idrep)}</td>
                            <td class="username">${escapeHtml(user.user_name)}</td>
                            <td>${escapeHtml(user.email || 'No email')}</td>
                            <td>${escapeHtml(managerName)}</td>
                            <td>${escapeHtml(user.rep_timestamp || 'Timestamp unavailable')}</td>
                            <td class="actions">${renderActions(user)}</td>
                        </tr>
                    `;
                }).join('');

                itemsContainer.innerHTML = html;
                table.trigger("update");
            };

            searchBox.addEventListener("input", (event) => {
                const userInput = event.target.value.trim().toLowerCase();
                const filteredUsers = users.filter((user) => {
                    return (user.email || '').toLowerCase().includes(userInput)
                        || (user.user_name || '').toLowerCase().includes(userInput)
                        || String(user.idrep).includes(userInput);
                });

                showUsers(filteredUsers);
            });

            table.tablesorter({
                sortList: [[0, 0]],
                widgets: ['staticRow']
            });

            showUsers(users);
        });
    </script>
@endsection
