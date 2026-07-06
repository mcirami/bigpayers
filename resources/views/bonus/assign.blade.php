@extends('layouts.dashboard-shell')

@section('page-title', 'Assign Bonus')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Bonuses Workspace</p>
                    <h2 class="bp-section-title">Assign users to {{ $bonus->name }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Select the users who should be eligible for this bonus.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/bonuses" class="bp-button-secondary">Back to bonuses</a>
                    <a href="/bonuses/{{ $bonus->id }}/edit" class="bp-button-primary">Edit bonus</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Sales Required</p>
                <p class="bp-stat-value">{{ $bonus->sales_required }}</p>
                <p class="bp-stat-note">Weekly sales threshold for this bonus.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Payout</p>
                <p class="bp-stat-value">${{ number_format((float) $bonus->payout, 2) }}</p>
                <p class="bp-stat-note">Bonus payout issued when achieved.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ (int) $bonus->is_active === 1 ? 'Active' : 'In-Active' }}</p>
                <p class="bp-stat-note">Inactive bonuses are ignored during processing.</p>
            </article>
        </section>

        <form method="post" action="/bonuses/{{ $bonus->id }}/assign" class="space-y-6">
            @csrf

            <section class="bp-card">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Assignments</p>
                        <h3 class="bp-section-title">Eligible users</h3>
                    </div>
                    <button type="submit" class="bp-button-primary">Save assignments</button>
                </div>

                <div class="mt-6 grid gap-5 xl:grid-cols-3">
                    @forelse($userGroups as $group)
                        <section class="rounded-md border border-slate-200/70 bg-white/60 p-4">
                            <div>
                                <p class="bp-section-kicker">{{ $group['name'] }}</p>
                                <h4 class="bp-selection-title">Assigned {{ strtolower($group['name']) }}</h4>
                            </div>

                            <div class="bp-checklist mt-5">
                                @foreach($group['users'] as $user)
                                    <label class="bp-checklist-item">
                                        <input class="fixCheckBox" type="checkbox" name="user_ids[]" value="{{ $user->idrep }}" @checked($user->assigned)>
                                        <span>{{ $user->user_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <div class="bp-inline-note">
                            <strong>No assignable users</strong>
                            <span>Your current permissions do not expose any user groups for assignment.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </form>
    </div>
@endsection
