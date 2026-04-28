@extends('layouts.dashboard-shell')

@section('page-title', 'Offer Details')

@section('content')
    @php
        $typeLabels = [
            \App\Offer::TYPE_PPS => 'PPS',
            \App\Offer::TYPE_PPC => 'PPC',
            \App\Offer::TYPE_PPL => 'PPL',
            \App\Offer::TYPE_DATING => 'Dating',
            \App\Offer::TYPE_CAMS => 'Cams',
            \App\Offer::TYPE_SWEEPS => 'Sweeps',
            \App\Offer::TYPE_NUTRA => 'Nutra',
        ];
        $visibilityLabels = [
            \App\Offer::VISIBILITY_PRIVATE => 'Private',
            \App\Offer::VISIBILITY_PUBLIC => 'Public',
            \App\Offer::VISIBILITY_REQUESTABLE => 'Requestable',
        ];
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review the current offer configuration, payout, advertiser assignment, and who currently has access from one place.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if(\LeadMax\TrackYourStats\System\Session::permissions()->can('create_offers'))
                        <a href="/offer/edit/{{ $offer->idoffer }}" class="bp-button-primary">Edit offer</a>
                    @endif

                    @if(\LeadMax\TrackYourStats\System\Session::permissions()->can('edit_offer_rules'))
                        <a href="/offer/rules/{{ $offer->idoffer }}" class="bp-button-secondary">Manage rules</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">ID</p>
                <p class="bp-stat-value">{{ $offer->idoffer }}</p>
                <p class="bp-stat-note">Primary offer identifier used throughout reports and tracking links.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Default Payout</p>
                <p class="bp-stat-value">${{ number_format((float) $offer->payout, 2) }}</p>
                <p class="bp-stat-note">Base payout before any role-specific or affiliate-specific overrides are applied.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ (int) $offer->status === 1 ? 'Active' : 'Disabled' }}</p>
                <p class="bp-stat-note">Controls whether the offer shows up in the active offer listings.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assigned Users</p>
                <p class="bp-stat-value">{{ $assignedUsers->count() }}</p>
                <p class="bp-stat-note">Current users who already have this offer in their available inventory.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
            <article class="bp-card">
                <p class="bp-section-kicker">Offer Details</p>
                <h3 class="bp-section-title">Configuration summary</h3>

                <div class="mt-6 grid gap-x-8 md:grid-cols-2">
                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Name</p>
                        <p class="bp-detail-value">{{ $offer->offer_name ?: 'Not set' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Advertiser</p>
                        <p class="bp-detail-value">{{ optional($offer->campaign)->name ?: 'Default advertiser' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Type</p>
                        <p class="bp-detail-value">{{ $typeLabels[$offer->offer_type] ?? 'Unknown' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Visibility</p>
                        <p class="bp-detail-value">{{ $visibilityLabels[$offer->is_public] ?? 'Unknown' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Affiliate Payout</p>
                        <p class="bp-detail-value">{{ $offer->affiliate_payout !== null ? '$' . number_format((float) $offer->affiliate_payout, 2) : 'Uses default payout' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Manager Payout</p>
                        <p class="bp-detail-value">{{ $offer->manager_payout !== null ? '$' . number_format((float) $offer->manager_payout, 2) : 'Uses default payout' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Admin Payout</p>
                        <p class="bp-detail-value">{{ $offer->admin_payout !== null ? '$' . number_format((float) $offer->admin_payout, 2) : 'Uses default payout' }}</p>
                    </div>

                    <div class="bp-detail-row">
                        <p class="bp-detail-label">Created</p>
                        <p class="bp-detail-value">{{ $offer->offer_timestamp ?: 'Not set' }}</p>
                    </div>

                    <div class="bp-detail-row md:col-span-2">
                        <p class="bp-detail-label">Destination URL</p>
                        <p class="bp-detail-value break-all">{{ $offer->url ?: 'Not set' }}</p>
                    </div>

                    <div class="bp-detail-row md:col-span-2">
                        <p class="bp-detail-label">Description</p>
                        <p class="bp-detail-value">{{ $offer->description ?: 'No description added yet.' }}</p>
                    </div>
                </div>
            </article>

            <article class="bp-card">
                <p class="bp-section-kicker">Assignment</p>
                <h3 class="bp-section-title">Current user access</h3>

                <div class="mt-6 bp-checklist">
                    @forelse($assignedUsers as $user)
                        <div class="bp-checklist-item">
                            <span>{{ $user->user_name }}</span>
                        </div>
                    @empty
                        <div class="bp-link-card">
                            <div>
                                <p class="bp-link-label">Availability</p>
                                <p class="bp-link-value">No users are currently assigned to this offer.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
@endsection
