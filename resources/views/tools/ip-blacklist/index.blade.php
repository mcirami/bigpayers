@extends('layouts.dashboard-shell')

@section('page-title', 'IP Blacklist')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Security Workspace</p>
                    <h2 class="bp-section-title value_span9">IP blacklist</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage the blocked IP ranges that prevent known bad traffic from registering clicks before it can affect reports, routing, or downstream tracking.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/ip-blacklist/create" class="bp-button-primary">Add IP range</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Ranges</p>
                <p class="bp-stat-value">{{ $entries->count() }}</p>
                <p class="bp-stat-note">Stored blacklist ranges currently active in the system.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Latest Entry</p>
                <p class="bp-stat-value">{{ $latestCreatedLabel }}</p>
                <p class="bp-stat-note">Most recently created blocked range on this install.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Scope</p>
                <p class="bp-stat-value">God</p>
                <p class="bp-stat-note">This utility remains restricted to the highest admin role.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Blacklist Table</p>
                    <h3 class="bp-section-title value_span9">Blocked ranges</h3>
                </div>
                <p class="bp-table-meta">Ranges are stored as start and end IP bounds, then evaluated during click registration.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table class="table table-striped table_01 large_table" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">Start</th>
                        <th class="value_span9">End</th>
                        <th class="value_span9">Created</th>
                        <th class="value_span9" data-sorter="false">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>{{ $entry->start }}</td>
                            <td>{{ $entry->end }}</td>
                            <td>{{ $entry->createdLabel }}</td>
                            <td>
                                <div class="bp-table-actions">
                                    <a href="/ip-blacklist/{{ $entry->id }}/edit" class="bp-action-link">Edit</a>
                                    <form method="post" action="/ip-blacklist/{{ $entry->id }}/delete" onsubmit="return confirm('Delete this blocked IP range?');">
                                        @csrf
                                        <button type="submit" class="bp-action-link value_span11 value_span2">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="bp-table-empty">No IP ranges have been blacklisted yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script>
        $('#mainTable').tablesorter({
            sortList: [[3, 1]],
            widgets: ['staticRow']
        });
    </script>
@endsection
