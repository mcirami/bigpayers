@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Advertisers')

@section('content')
    @php
        $totalAdvertisers = $campaigns->count();
        $advertisersWithOffers = $campaigns->where('offers_count', '>', 0)->count();
        $totalAssignedOffers = $campaigns->sum('offers_count');
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Advertisers Workspace</p>
                    <h2 class="bp-section-title value_span9">Advertiser directory</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review advertiser accounts, see how many offers each one currently owns, and jump into edits without leaving the new shell.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/advertisers/create" class="bp-button-primary">Create advertiser</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Advertisers</p>
                <p class="bp-stat-value">{{ $totalAdvertisers }}</p>
                <p class="bp-stat-note">Total advertiser records currently configured in the platform.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">With Offers</p>
                <p class="bp-stat-value">{{ $advertisersWithOffers }}</p>
                <p class="bp-stat-note">Advertisers that already have one or more offers attached.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assigned Offers</p>
                <p class="bp-stat-value">{{ $totalAssignedOffers }}</p>
                <p class="bp-stat-note">Combined offer count across all advertisers in this directory.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Advertiser Registry</p>
                    <h3 class="bp-section-title value_span9">Searchable advertiser table</h3>
                </div>
                <p class="bp-table-meta">Sorting is still powered by the existing tablesorter scripts while the page layout is upgraded.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-condensed table-bordered table_01 tablesorter" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">Name</th>
                        <th class="value_span9">Offers</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->id }}</td>
                            <td>{{ $campaign->name }}</td>
                            <td>{{ $campaign->offers_count }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4" href="/advertisers/{{ $campaign->id }}/edit">Edit</a>
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
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
