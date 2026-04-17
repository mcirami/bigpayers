@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Banned Users')

@section('content')
    @php
        $activeBans = $bans->filter(fn ($ban) => (int) $ban->status === 1 && $ban->expires > now()->format('Y-m-d H:i:s'))->count();
        $expiredBans = $bans->count() - $activeBans;
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">Banned user directory</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review active and historical bans, check expiration timing, and jump directly into ban settings when an update is needed.
                    </p>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    Moderation view • {{ $bans->count() }} records
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Total Records</p>
                <p class="bp-stat-value">{{ $bans->count() }}</p>
                <p class="bp-stat-note">All ban rows visible inside your ownership tree.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active Bans</p>
                <p class="bp-stat-value">{{ $activeBans }}</p>
                <p class="bp-stat-note">Bans that are still active and not expired yet.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Inactive</p>
                <p class="bp-stat-value">{{ $expiredBans }}</p>
                <p class="bp-stat-note">Expired or manually disabled ban records.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Moderation Log</p>
                    <h3 class="bp-section-title value_span9">Banned users table</h3>
                </div>
                <p class="bp-table-meta">This mirrors the old moderation data but keeps the new table styling and navigation shell.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-bordered table_01 tablesorter" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">{{ $affiliateTypeLabel }}</th>
                        <th class="value_span9">Banned</th>
                        <th class="value_span9">Expires</th>
                        <th class="value_span9">Reason</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bans as $ban)
                        @php
                            $isActive = (int) $ban->status === 1 && $ban->expires > now()->format('Y-m-d H:i:s');
                        @endphp
                        <tr>
                            <td>{{ $ban->user_id }}</td>
                            <td>{{ $ban->user_name }}</td>
                            <td>{{ $ban->timestamp }}</td>
                            <td>{{ $ban->expires }}</td>
                            <td>{{ $ban->reason ?: 'No reason provided' }}</td>
                            <td>{{ $isActive ? 'Active' : 'Inactive' }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4" href="/user/{{ $ban->user_id }}/ban/edit">Ban settings</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter({
                sortList: [[2, 1]],
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
