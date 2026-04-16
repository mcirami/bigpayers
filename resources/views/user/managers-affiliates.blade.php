@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Manager Team')

@section('content')
    @php
        $visibleAffiliates = $affiliates->count();
        $totalAffiliates = method_exists($affiliates, 'total') ? $affiliates->total() : $visibleAffiliates;
        $activeAffiliates = $affiliates->where('status', 1)->count();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $manager->user_name }}'s affiliates</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review the agents assigned to this manager, jump into edits, and use the existing pagination controls without leaving the new shell.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/manage?role={{ \App\Privilege::ROLE_MANAGER }}" class="bp-button-secondary">Back to managers</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visible Affiliates</p>
                <p class="bp-stat-value">{{ $visibleAffiliates }}</p>
                <p class="bp-stat-note">Affiliates currently shown on this page of the manager drilldown.</p>
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
                <p class="bp-stat-value">{{ request()->query('rpp', 10) }}</p>
                <p class="bp-stat-note">Adjustable through the legacy pagination controls below the directory.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Team Directory</p>
                    <h3 class="bp-section-title value_span9">Manager affiliate roster</h3>
                </div>
                <p class="bp-table-meta">The table remains compatible with the existing pagination partial and account action shortcuts.</p>
            </div>

            <div class="mt-6 bp-offer-search">
                <label class="bp-detail-label" for="affiliateSearch">Search affiliates</label>
                <input
                    id="affiliateSearch"
                    class="bp-search-input"
                    type="text"
                    placeholder="Search by name, username, phone, or ID"
                >
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-bordered table-striped table_01" id="managerAffiliatesTable">
                    <thead>
                    <tr>
                        <th class="value_span9">Aff ID</th>
                        <th class="value_span9">First Name</th>
                        <th class="value_span9">Last Name</th>
                        <th class="value_span9">Cell Phone</th>
                        <th class="value_span9">Username</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Referrer</th>
                        <th class="value_span9">Aff Timestamp</th>
                        <th class="value_span9">Actions</th>
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
                            <td>{{ optional($affiliate->referrer)->user_name ?: 'No manager assigned' }}</td>
                            <td>{{ $affiliate->rep_timestamp }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="bp-action-link value_span6-1 value_span4" href="/aff_update.php?idrep={{ $affiliate->idrep }}">Edit</a>
                                    <a class="bp-action-link value_span5-1" href="#" onclick="adminLogin({{ $affiliate->idrep }}); return false;">Login</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bp-legacy-pagination">
                @include('report.options.pagination')
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
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
