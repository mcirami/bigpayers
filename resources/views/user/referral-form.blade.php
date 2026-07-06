@extends('layouts.dashboard-shell')

@section('page-title', 'Add Referral')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title">Add referral for {{ $referrer->user_name }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Attach a new referred affiliate to this user and define the payout structure from the same modern shell as the rest of user management.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/{{ $referrer->idrep }}/referrals" class="bp-button-secondary">Back to referrals</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Referrer</p>
                <p class="bp-stat-value">{{ $referrer->user_name }}</p>
                <p class="bp-stat-note">Affiliate that will receive the referral credit.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Available {{ $affiliateTypeLabelPlural }}</p>
                <p class="bp-stat-value">{{ $availableAffiliates->count() }}</p>
                <p class="bp-stat-note">Eligible affiliates that are not already assigned inside another referral structure.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ $availableAffiliates->isNotEmpty() ? 'Ready' : 'Full' }}</p>
                <p class="bp-stat-note">Create is available while there are unassigned affiliates left to attach.</p>
            </article>
        </section>

        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Referral Setup</p>
                    <h3 class="bp-section-title">Create a referral structure</h3>
                </div>
                <p class="bp-table-meta">Set the affiliate, referral window, commission type, and payout value for this structure.</p>
            </div>

            @if($availableAffiliates->isNotEmpty())
                <form action="/user/{{ $referrer->idrep }}/referrals/create" method="post" class="mt-6 space-y-5">
                    {{ csrf_field() }}

                    <div class="bp-form-grid md:grid-cols-2">
                        <label class="bp-form-field">
                            <span class="bp-form-label">Affiliate to Refer</span>
                            <select class="bp-form-input" name="toRefer" required>
                                @foreach($availableAffiliates as $affiliate)
                                    <option value="{{ $affiliate->idrep }}" {{ (int) old('toRefer') === (int) $affiliate->idrep ? 'selected' : '' }}>{{ $affiliate->user_name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="bp-form-field">
                            <span class="bp-form-label">Start Date</span>
                            <input class="bp-form-input" id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required>
                        </label>

                        <label class="bp-form-field">
                            <span class="bp-form-label">End Date</span>
                            <input class="bp-form-input" id="end_date" name="end_date" type="date" value="{{ old('end_date') }}">
                        </label>

                        <label class="bp-form-field">
                            <span class="bp-form-label">Referral Type</span>
                            <select class="bp-form-input" name="referral_type" required>
                                <option value="flat" {{ old('referral_type') === 'flat' ? 'selected' : '' }}>Flat Fee</option>
                                <option value="percentage" {{ old('referral_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            </select>
                        </label>

                        <label class="bp-form-field bp-form-field-full">
                            <span class="bp-form-label">Amount</span>
                            <input class="bp-form-input" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', 0) }}" required>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="bp-button-primary">Create referral</button>
                        <a href="/user/{{ $referrer->idrep }}/referrals" class="bp-button-secondary">Cancel</a>
                    </div>
                </form>
            @else
                <div class="mt-6 bp-inline-note">
                    <strong>No available affiliates</strong>
                    <span>Every eligible affiliate in your tree is already part of a referral structure or is the selected referrer.</span>
                </div>
            @endif
        </section>
    </div>
@endsection
