@extends('layouts.dashboard-shell')

@section('page-title', 'Email Pools')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Lead Inventory</p>
                    <h2 class="bp-section-title">Email pools</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Download pools already assigned to you or claim an available pool from the shared inventory.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Owned</p>
                        <p class="bp-link-value">{{ $ownedPools->count() }}</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Available</p>
                        <p class="bp-link-value">{{ $availablePools->count() }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Owned Pools</p>
                    <h3 class="bp-section-title">Ready to download</h3>
                    <p class="bp-table-meta mt-3">These pools are already assigned to your account.</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table>
                    <thead>
                    <tr>
                        <th>Pool</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($ownedPools as $pool)
                        <tr>
                            <td>Pool #{{ $pool->id }}</td>
                            <td>{{ $pool->timestamp }}</td>
                            <td>
                                <div class="bp-table-actions">
                                    <a class="bp-action-link" href="/email/pools/{{ $pool->id }}/download">Download</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="bp-table-empty">No email pools have been claimed yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Available Pools</p>
                    <h3 class="bp-section-title">Open inventory</h3>
                    <p class="bp-table-meta mt-3">Claiming a pool assigns it to your account for download.</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table>
                    <thead>
                    <tr>
                        <th>Pool</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($availablePools as $pool)
                        <tr>
                            <td>Pool #{{ $pool->id }}</td>
                            <td>{{ $pool->timestamp }}</td>
                            <td>
                                <div class="bp-table-actions">
                                    <a class="bp-action-link" href="/email/pools/{{ $pool->id }}/claim">Claim</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="bp-table-empty">No email pools are available right now.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
