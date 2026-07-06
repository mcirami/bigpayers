@extends('layouts.dashboard-shell')

@section('page-title', $accountTypeLabel . ' Team')

@section('content')
    @php
        $visibleAffiliates = $affiliates->count();
        $totalAffiliates = method_exists($affiliates, 'total') ? $affiliates->total() : $visibleAffiliates;
        $activeAffiliates = $affiliates->where('status', 1)->count();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title">{{ $manager->user_name }}'s {{ strtolower($affiliateTypeLabelPlural) }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review the {{ strtolower($affiliateTypeLabelPlural) }} assigned to this {{ strtolower($accountTypeLabel) }}, jump into edits, and use the existing pagination controls.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/manage?role={{ \App\Privilege::ROLE_MANAGER }}" class="bp-button-secondary">Back to {{ strtolower($accountTypeLabelPlural) }}</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visible {{ $affiliateTypeLabelPlural }}</p>
                <p class="bp-stat-value">{{ $visibleAffiliates }}</p>
                <p class="bp-stat-note">{{ $affiliateTypeLabelPlural }} currently shown on this page of the {{ strtolower($accountTypeLabel) }} drilldown.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Total Matching</p>
                <p class="bp-stat-value">{{ $totalAffiliates }}</p>
                <p class="bp-stat-note">Full result count for this manager across all pagination pages.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active Status</p>
                <p class="bp-stat-value">{{ $activeAffiliates }}</p>
                <p class="bp-stat-note">Rows in the current page whose account status is active.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Rows Per Page</p>
                <p class="bp-stat-value">{{ $rowsPerPage }}</p>
                <p class="bp-stat-note">Adjustable through the pagination controls below the directory.</p>
            </article>
        </section>

        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Team Directory</p>
                    <h3 class="bp-section-title">{{ $accountTypeLabel }} {{ strtolower($affiliateTypeLabel) }} roster</h3>
                </div>
                <p class="bp-table-meta">The table remains compatible with the existing pagination partial and account action shortcuts.</p>
            </div>

            <div class="mt-6 bp-offer-search">
                <label class="bp-detail-label" for="affiliateSearch">Search {{ strtolower($affiliateTypeLabelPlural) }}</label>
                <input
                    id="affiliateSearch"
                    class="bp-search-input"
                    type="text"
                    placeholder="Search by name, username, phone, or ID"
                >
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table  id="managerAffiliatesTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>First</th>
                        <th>Last</th>
                        <th>Phone</th>
                        <th>{{ $affiliateTypeLabel }}</th>
                        <th>Status</th>
                        <th>Referrer</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="affiliateRows">
                    @foreach($affiliates as $affiliate)
                        <tr>
                            <td>{{ $affiliate->idrep }}</td>
                            <td>{{ $affiliate->first_name }}</td>
                            <td>{{ $affiliate->last_name }}</td>
                            <td>{{ $affiliate->cell_phone }}</td>
                            <td>{{ $affiliate->user_name }}</td>
                            <td>{{ $affiliate->status }}</td>
                            <td>{{ optional($affiliate->referrer)->user_name ?: 'No ' . strtolower($accountTypeLabel) . ' assigned' }}</td>
                            <td>{{ $affiliate->rep_timestamp }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="bp-action-link" href="/user/{{ $affiliate->idrep }}/edit">Edit</a>
                                    <a class="bp-action-link" href="#" onclick="adminLogin({{ $affiliate->idrep }}); return false;">Login</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bp-report-pagination">
                @include('report.options.pagination')
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        function adminLogin(id) {
            window.open('/login/' + id);
        }

        (() => {
            const searchInput = document.getElementById('affiliateSearch');
            const rows = Array.from(document.querySelectorAll('#affiliateRows tr'));

            if (!searchInput || !rows.length) {
                return;
            }

            searchInput.addEventListener('input', (event) => {
                const query = event.target.value.trim().toLowerCase();

                rows.forEach((row) => {
                    const matches = row.textContent.toLowerCase().includes(query);
                    row.style.display = matches ? '' : 'none';
                });
            });
        })();
    </script>
@endsection
