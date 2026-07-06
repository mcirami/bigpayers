@extends('layouts.dashboard-shell')

@section('page-title', $pageTitle)

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Provisioning Workspace</p>
                    <h2 class="bp-section-title">Database updates</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review company install database versions and run the existing updater through Laravel.
                    </p>
                </div>

                <form method="post" action="/admin/database-updates">
                    @csrf
                    <button type="submit" class="bp-button-primary">Run database updates</button>
                </form>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Companies</p>
                <p class="bp-stat-value">{{ $totalCompanies }}</p>
                <p class="bp-stat-note">Install records inspected.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Required</p>
                <p class="bp-stat-value">{{ $totalRequired }}</p>
                <p class="bp-stat-note">Pending updater versions.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Succeeded</p>
                <p class="bp-stat-value">{{ $totalSuccess }}</p>
                <p class="bp-stat-note">Applied in the latest run.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Failed</p>
                <p class="bp-stat-value">{{ $totalFailure }}</p>
                <p class="bp-stat-note">Needs manual review.</p>
            </article>
        </section>

        @if($error)
            <section class="bp-card border border-red-200">
                <p class="bp-section-kicker text-red-700">Updater Error</p>
                <h3 class="bp-section-title">Unable to inspect database updates</h3>
                <p class="mt-3 text-sm leading-7 text-red-700">{{ $error }}</p>
            </section>
        @else
            <section class="bp-card">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">{{ $didRun ? 'Run Results' : 'Preview' }}</p>
                        <h3 class="bp-section-title">Company updater report</h3>
                    </div>
                    <p class="bp-table-meta">
                        {{ $didRun ? 'The updater has been executed for each listed install.' : 'No changes have been applied from this preview.' }}
                    </p>
                </div>

                <div class="mt-6 bp-report-table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Install</th>
                            <th>Latest Version</th>
                            <th>Required</th>
                            <th>Valid</th>
                            @if($didRun)
                                <th>Succeeded</th>
                                <th>Failed</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($report as $company)
                            <tr>
                                <td>{{ $company['subDomain'] }}</td>
                                <td>{{ $company['latestVersion'] ?? 'Unknown' }}</td>
                                <td>
                                    <div class="space-y-1">
                                        <strong>{{ $company['requiredCount'] }}</strong>
                                        @foreach($company['required'] as $version)
                                            <div class="text-xs text-slate-500">{{ $version['name'] }}{{ $version['version'] ? ' (' . $version['version'] . ')' : '' }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>{{ $company['validCount'] }}</td>
                                @if($didRun)
                                    <td>
                                        <div class="space-y-1">
                                            <strong class="text-green-700">{{ $company['successCount'] }}</strong>
                                            @foreach($company['success'] as $version)
                                                <div class="text-xs text-slate-500">{{ $version }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1">
                                            <strong class="{{ $company['failureCount'] > 0 ? 'text-red-700' : 'text-slate-700' }}">{{ $company['failureCount'] }}</strong>
                                            @foreach($company['failure'] as $version)
                                                <div class="text-xs text-red-700">{{ $version }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $didRun ? 6 : 4 }}">No company installs were returned by the updater.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
@endsection
