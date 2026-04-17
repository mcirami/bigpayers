@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Offer URLs')

@section('content')
    @php
        $activeUrls = collect($urls)->where('status', 1)->count();
        $inactiveUrls = count($urls) - $activeUrls;
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">Offer URL directory</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage the branded domains used for outbound offer links, keep active URLs easy to scan, and jump into the existing create or edit tools when you need to change one.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/urls/create" class="bp-button-primary">Create new URL</a>
                    <a href="/offer/manage" class="bp-button-secondary">Back to offers</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Tracked URLs</p>
                <p class="bp-stat-value">{{ count($urls) }}</p>
                <p class="bp-stat-note">Total offer-link domains currently configured for this company.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active URLs</p>
                <p class="bp-stat-value">{{ $activeUrls }}</p>
                <p class="bp-stat-note">Domains that are currently live and ready to be selected in the offer flow.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Inactive URLs</p>
                <p class="bp-stat-value">{{ $inactiveUrls }}</p>
                <p class="bp-stat-note">Saved domains that are paused and no longer exposed in active offer selections.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">URL Registry</p>
                    <h3 class="bp-section-title value_span9">Searchable offer URL table</h3>
                </div>
                <p class="bp-table-meta">Sorting is still powered by the existing tablesorter scripts while the page layout is upgraded.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-bordered table_01 tablesorter" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">URL</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Added</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($urls as $url)
                        <tr>
                            <td>{{ $url['url'] }}</td>
                            <td>
                                <span class="bp-status-pill {{ (int) $url['status'] === 1 ? 'bp-status-pill-active' : '' }}">
                                    {{ (int) $url['status'] === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $url['timestamp'])->toFormattedDateString() }}</td>
                            <td class="actions">
                                <div class="bp-table-actions">
                                    <a class="btn btn-default btn-sm value_span6-1 value_span4" href="/offer/urls/{{ $url['id'] }}/edit">Edit</a>
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
