@extends('layouts.dashboard-shell')

@section('page-title', 'Users')

@section('content')
    @php
        $roleLabels = [
            \App\Privilege::ROLE_GOD => 'God',
            \App\Privilege::ROLE_ADMIN => 'Admins',
            \App\Privilege::ROLE_MANAGER => $accountTypeLabelPlural,
            \App\Privilege::ROLE_AFFILIATE => $affiliateTypeLabelPlural,
        ];
        $selectedRoleLabel = $roleLabels[$role] ?? 'Users';
        $tableIdentityLabel = match ($role) {
            \App\Privilege::ROLE_MANAGER => $accountTypeLabel,
            \App\Privilege::ROLE_AFFILIATE => $affiliateTypeLabel,
            default => 'User',
        };
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
                        Review account access, jump into edits, and log into downstream users.
                    </p>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    {{ $selectedRoleLabel }} • {{ $showInactive ? 'Inactive' : 'Active' }}
                </div>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="bp-report-toolbar">
                    @include('report.options.user-type')
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
                <p class="bp-table-meta">Use search to filter the directory, then sort any column in place.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table class="manage_user_table" id="mainTable" data-sortable-table data-sort-default="0:asc">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">{{ $tableIdentityLabel }}</th>
                        <th class="value_span9">Email</th>
                        <th class="value_span9">{{ $accountTypeLabel }}</th>
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
        (() => {
            const canEditAffiliates = @json($canEditAffiliates);
            const canCreateAffiliates = @json($canCreateAffiliates);
            const canCreateManagers = @json($canCreateManagers);
            const canBanUsers = @json($canBanUsers);
            const role = @json($role);
            const users = @json($users);
            const itemsContainer = document.querySelector("#users_container");
            const searchBox = document.getElementById("searchBox");

            window.adminLogin = (id) => {
                window.open('/login/' + id);
            };

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
                        `<a class="bp-action-link" href="/user/${user.idrep}/edit">Edit</a>`
                    );
                }

                if (canCreateAffiliates) {
                    actions.push(
                        `<a class="bp-action-link" href="#" onclick="adminLogin(${user.idrep}); return false;">Login</a>`
                    );
                }

                if (canBanUsers) {
                    actions.push(
                        `<a class="bp-action-link" href="/user/${user.idrep}/ban">Ban</a>`
                    );
                }

                if (!actions.length) {
                    actions.push('<span class="bp-table-empty">No actions</span>');
                }

                return `<div class="bp-table-actions">${actions.join('')}</div>`;
            };

            const showUsers = (userRows) => {
                const html = userRows.map((user) => {
                    const managerName = user.referrer && user.referrer.user_name ? user.referrer.user_name : 'No {{ strtolower($accountTypeLabel) }} assigned';

                    return `
                        <tr>
                            <td>${escapeHtml(user.idrep)}</td>
                            <td class="username">${escapeHtml(user.user_name)}</td>
                            <td>${escapeHtml(user.email || 'No email')}</td>
                            <td>${escapeHtml(managerName)}</td>
                            <td class="actions">${renderActions(user)}</td>
                        </tr>
                    `;
                }).join('');

                itemsContainer.innerHTML = html;
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

            showUsers(users);
        })();
    </script>
    @include('layouts.partials.sortable-table-script')
@endsection
