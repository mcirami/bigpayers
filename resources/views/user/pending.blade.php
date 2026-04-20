@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Pending Users')

@section('content')
    @php
        $totalPending = $users->count();
        $withCompany = $users->filter(fn ($user) => filled($user->company_name))->count();
        $withSkype = $users->filter(fn ($user) => filled($user->skype))->count();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">Pending {{ strtolower($affiliateTypeLabelPlural) }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review new signups waiting for approval and activate them into the right {{ strtolower($accountTypeLabel) }} without leaving the new shell.
                    </p>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    Approval queue • {{ $totalPending }} waiting
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Pending</p>
                <p class="bp-stat-value">{{ $totalPending }}</p>
                <p class="bp-stat-note">Signups currently waiting for approval.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Company Profiles</p>
                <p class="bp-stat-value">{{ $withCompany }}</p>
                <p class="bp-stat-note">Pending signups that already included a company name.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Skype Added</p>
                <p class="bp-stat-value">{{ $withSkype }}</p>
                <p class="bp-stat-note">Profiles that already shared a Skype contact.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Approval Queue</p>
                    <h3 class="bp-section-title value_span9">Pending signup table</h3>
                </div>
                <p class="bp-table-meta">Activate moves the signup into the live tree and applies the standard affiliate setup.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-striped table_01 tablesorter" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">{{ $affiliateTypeLabel }}</th>
                        <th class="value_span9">Name</th>
                        <th class="value_span9">Email</th>
                        <th class="value_span9">Company</th>
                        <th class="value_span9">Requested</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->idrep }}</td>
                            <td>{{ $user->user_name }}</td>
                            <td>{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'No name provided' }}</td>
                            <td>{{ $user->email ?: 'No email' }}</td>
                            <td>{{ $user->company_name ?: 'No company' }}</td>
                            <td>{{ $user->rep_timestamp ?: 'Unknown' }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4" href="/user/pending/{{ $user->idrep }}/activate">Activate</a>
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
                sortList: [[5, 1]],
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
