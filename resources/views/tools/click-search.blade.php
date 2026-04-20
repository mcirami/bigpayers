@extends('layouts.dashboard-shell')

@section('page-title', 'Click Search')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Lookup Workspace</p>
                    <h2 class="bp-section-title value_span9">Click search</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Search by either the raw click ID or the encoded tracking ID, then inspect the click record, geo resolution, stored query vars, and any attached conversion without dropping into the legacy page.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Input</p>
                        <p class="bp-link-value">Encoded or numeric</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Scope</p>
                        <p class="bp-link-value">God-only lookup utility</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Status</p>
                        <p class="bp-link-value">{{ $searchAttempted ? ($clickFound ? 'Record found' : 'No match yet') : 'Ready to search' }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Search</p>
                    <h3 class="bp-section-title value_span9">Find a click record</h3>
                </div>
                <p class="bp-table-meta">Paste a click ID from the UI, postback, or reporting export and this tool will normalize the lookup for you.</p>
            </div>

            <form method="get" action="/click-search" class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                <div class="bp-form-field">
                    <label class="bp-form-label" for="clickId">Click ID</label>
                    <input
                        id="clickId"
                        name="clickId"
                        type="text"
                        class="bp-form-input"
                        value="{{ $searchValue }}"
                        placeholder="Encoded or decoded click ID..."
                        autocomplete="off"
                    >
                    <p class="bp-form-note">Supports the same encoded IDs the legacy tool accepted.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="bp-button-primary">Search</button>
                    @if ($searchAttempted)
                        <a href="/click-search" class="bp-button-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        @if ($searchAttempted && !$clickFound)
            <section class="bp-card value_span8">
                <p class="bp-section-kicker">No Match</p>
                <h3 class="bp-section-title value_span9">No click was found for that lookup</h3>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                    Double-check the click ID, or try the alternate format if you copied an encoded ID from a postback instead of the raw click number.
                </p>
            </section>
        @endif

        @if ($clickFound)
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="bp-stat-card">
                    <p class="bp-stat-label">Lookup</p>
                    <p class="bp-stat-value">{{ $lookupMode }}</p>
                    <p class="bp-stat-note">Original search format used for this result.</p>
                </article>

                <article class="bp-stat-card">
                    <p class="bp-stat-label">Decoded ID</p>
                    <p class="bp-stat-value">{{ $decodedClickId }}</p>
                    <p class="bp-stat-note">Canonical click record used for the database lookup.</p>
                </article>

                <article class="bp-stat-card">
                    <p class="bp-stat-label">Encoded Alias</p>
                    <p class="bp-stat-value">{{ $encodedAlias ?: '—' }}</p>
                    <p class="bp-stat-note">Useful when you need to jump back to a postback-safe click token.</p>
                </article>

                <article class="bp-stat-card">
                    <p class="bp-stat-label">Conversion</p>
                    <p class="bp-stat-value">{{ count($conversionData) ? 'Present' : 'None' }}</p>
                    <p class="bp-stat-note">Shows whether this click already has a stored conversion row.</p>
                </article>
            </section>

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Click Record</p>
                        <h3 class="bp-section-title value_span9">Primary click data</h3>
                    </div>
                </div>

                <div class="mt-6 bp-report-table-wrap">
                    <table class="table table-striped table_01 large_table">
                        <thead>
                        <tr>
                            @foreach (array_keys($clickData) as $label)
                                <th class="value_span9" data-sorter="false">{{ $label }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            @foreach ($clickData as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Geo Data</p>
                        <h3 class="bp-section-title value_span9">Resolved location</h3>
                    </div>

                    <div class="mt-6 bp-report-table-wrap">
                        <table class="table table-striped table_01 large_table">
                            <thead>
                            <tr>
                                @foreach (array_keys($geoData) as $label)
                                    <th class="value_span9" data-sorter="false">{{ $label }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach ($geoData as $value)
                                    <td>{{ $value }}</td>
                                @endforeach
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Conversion</p>
                        <h3 class="bp-section-title value_span9">Attached sale data</h3>
                    </div>

                    @if ($conversionData)
                        <div class="mt-6 bp-report-table-wrap">
                            <table class="table table-striped table_01 large_table">
                                <thead>
                                <tr>
                                    @foreach (array_keys($conversionData) as $label)
                                        <th class="value_span9" data-sorter="false">{{ $label }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    @foreach ($conversionData as $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="mt-6 bp-link-card">
                            <p class="bp-link-label">Status</p>
                            <p class="bp-link-value">No conversion is stored for this click.</p>
                        </div>
                    @endif
                </section>
            </div>

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Stored Query</p>
                        <h3 class="bp-section-title value_span9">Captured URL variables</h3>
                    </div>
                    <p class="bp-table-meta">{{ $queryVarCount }} populated values were captured with this click.</p>
                </div>

                <div class="mt-6 bp-report-table-wrap">
                    <table class="table table-striped table_01 large_table">
                        <thead>
                        <tr>
                            @foreach (array_keys($queryVars) as $label)
                                <th class="value_span9" data-sorter="false">{{ $label }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            @foreach ($queryVars as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
