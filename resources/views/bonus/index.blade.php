@extends('layouts.dashboard-shell')

@section('page-title', 'Bonuses')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Bonuses Workspace</p>
                    <h2 class="bp-section-title value_span9">Bonuses</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Manage bonus definitions, affiliate assignments, and manual bonus checks.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if($canProcessBonuses)
                        <form method="post" action="/bonuses/process">
                            @csrf
                            <button type="submit" class="bp-button-secondary">Force check bonuses</button>
                        </form>
                    @endif
                    @if($canCreateBonuses)
                        <a href="/bonuses/create" class="bp-button-primary">Create bonus</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Bonuses</p>
                <p class="bp-stat-value">{{ $bonuses->count() }}</p>
                <p class="bp-stat-note">Bonus records visible to your account.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active</p>
                <p class="bp-stat-value">{{ $bonuses->where('is_active', 1)->count() }}</p>
                <p class="bp-stat-note">Eligible for weekly processing.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Inactive</p>
                <p class="bp-stat-value">{{ $bonuses->where('is_active', 0)->count() }}</p>
                <p class="bp-stat-note">Kept for history or future reactivation.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="bp-report-table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Sales Required</th>
                        <th>Payout</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bonuses as $bonus)
                        <tr>
                            <td>{{ $bonus['id'] }}</td>
                            <td>{{ $bonus['name'] }}</td>
                            <td>{{ $bonus['sales_required'] }}</td>
                            <td>${{ number_format((float) $bonus['payout'], 2) }}</td>
                            <td>
                                <span class="{{ (int) $bonus['is_active'] === 1 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ (int) $bonus['is_active'] === 1 ? 'Active' : 'In-Active' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @if($canCreateBonuses)
                                        <a href="/bonuses/{{ $bonus['id'] }}/edit" class="bp-button-secondary">Edit</a>
                                    @endif
                                    @if($canAssignBonuses)
                                        <a href="/bonuses/{{ $bonus['id'] }}/assign" class="bp-button-secondary">Assign</a>
                                    @endif
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
