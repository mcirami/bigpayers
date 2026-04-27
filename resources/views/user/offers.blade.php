@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'User Offers')

@section('content')
    @php
        $canEditAffiliatePayout = \LeadMax\TrackYourStats\System\Session::permissions()->can('edit_aff_payout');
        $canManageOfferCaps = \LeadMax\TrackYourStats\System\Session::userType() === \App\Privilege::ROLE_GOD;
        $accessibleOffers = collect($offers)->where('has_offer', true)->count();
        $customPayoutOffers = collect($offers)->filter(fn ($offer) => $offer->reppayout !== null)->count();
    @endphp

    <div id="error_message" class="bp-error-banner">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16zM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12z" fill="currentColor"/>
            <path d="M12 14a1 1 0 0 1-1-1V7a1 1 0 1 1 2 0v6a1 1 0 0 1-1 1zm-1.5 2.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0z" fill="currentColor"/>
        </svg>
        <p></p>
    </div>

    <div id="user_info" class="edit_user_offers space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $name }}'s offer access</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review assigned inventory, update affiliate-specific payouts, and toggle access from one place without leaving the redesigned shell.
                    </p>
                </div>

                @include('user.partials.account-actions', [
                    'managedUser' => $managedUser,
                    'canManageOffers' => $canManageOffers,
                    'canManageSubIds' => $canManageSubIds,
                    'canLoginAsUser' => $canLoginAsUser,
                    'currentWorkspace' => 'offers',
                ])
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="rounded-3xl border border-slate-200/70 bg-white/70 p-4 shadow-sm">
                    <p class="bp-detail-label">Workflow</p>
                    <p class="mt-2 text-sm leading-7 text-slate-500">
                        Payout and access updates still use the existing account-management endpoints, so this page remains fully compatible with the legacy admin logic.
                    </p>
                </div>

                <div class="bp-offer-search">
                    <label class="bp-detail-label" for="offerSearch">Search offers</label>
                    <input
                        id="offerSearch"
                        class="bp-search-input"
                        type="text"
                        placeholder="Search by offer name or ID"
                    >
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Visible Offers</p>
                <p class="bp-stat-value">{{ count($offers) }}</p>
                <p class="bp-stat-note">All active offers currently available to review for this affiliate.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assigned Access</p>
                <p class="bp-stat-value">{{ $accessibleOffers }}</p>
                <p class="bp-stat-note">Offers that already have access enabled for this user.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Custom Payouts</p>
                <p class="bp-stat-value">{{ $customPayoutOffers }}</p>
                <p class="bp-stat-note">Rows where the affiliate payout differs from the base offer payout.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Editing</p>
                <p class="bp-stat-value">{{ $canEditAffiliatePayout ? 'Enabled' : 'Read only' }}</p>
                <p class="bp-stat-note">Your permission set determines whether payout and access controls stay interactive.</p>
            </article>
        </section>

        <section class="bp-card value_span8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Offer Controls</p>
                    <h3 class="bp-section-title value_span9">User offer matrix</h3>
                </div>
                <p class="bp-table-meta">Payout changes save on enter or blur, and access toggles continue using the existing AJAX handlers.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap white_box_x_scroll">
                <table class="table table-striped table_01 large_table bp-user-offers-table" id="mainTable">
                    <thead>
                    <tr>
                        <th class="value_span9">ID</th>
                        <th class="value_span9">Name</th>
                        <th class="value_span9">Payout</th>
                        <th class="value_span9">Custom $</th>
                        <th class="value_span9">Access</th>
                        @if ($canManageOfferCaps)
                            <th class="value_span9">Cap</th>
                            <th class="value_span9">Daily Max</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody id="userOfferRows">
                    @foreach($offers as $offer)
                        @php
                            $hasAccess = $offer->has_offer ? 'checked' : '';
                            $capEnabled = !empty($offer->cap_enabled) ? 'checked' : '';
                        @endphp
                        <tr data-search="{{ strtolower($offer->offer_name . ' ' . $offer->idoffer) }}">
                            <td>{{ $offer->idoffer }}</td>
                            <td>{{ $offer->offer_name }}</td>
                            <td>${{ number_format((float) $offer->payout, 2) }}</td>

                            @if ($canEditAffiliatePayout)
                                <td>
                                    @php
                                        $fallbackPayoutDisplay = '$' . number_format((float) $offer->effective_payout, 2);
                                        $hasCustomPayout = $offer->reppayout !== null;
                                    @endphp
                                    <div class="bp-custom-payout-field {{ $hasCustomPayout ? 'is-custom' : 'is-fallback' }}">
                                        <div class="bp-input-prefix-wrap">
                                        <input
                                            class="update_aff_payout bp-input-compact bp-input-compact-prefixed"
                                            type="number"
                                            step="0.25"
                                            id="offer_{{ $offer->idoffer }}"
                                            data-offer="{{ $offer->idoffer }}"
                                            data-rep="{{ $offer->idrep }}"
                                            data-fallback-display="{{ $fallbackPayoutDisplay }}"
                                            value="{{ $offer->reppayout ?? '' }}"
                                            placeholder=""
                                        />
                                    </div>
                                        <p class="bp-custom-payout-hint">
                                            {{ $hasCustomPayout ? 'Custom override active' : 'Using fallback: ' . $fallbackPayoutDisplay }}
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    <label class="offer_access bp-toggle-inline" for="offer_access_{{ $offer->idoffer }}">
                                        <input
                                            class="offer_access_check"
                                            type="checkbox"
                                            id="offer_access_{{ $offer->idoffer }}"
                                            data-rep="{{ $offer->idrep }}"
                                            data-offer="{{ $offer->idoffer }}"
                                            name="offer_access"
                                            {{ $hasAccess }}
                                        >
                                        <span>{{ $offer->has_offer ? 'Enabled' : 'Disabled' }}</span>
                                    </label>
                                </td>
                            @else
                                <td>${{ number_format((float) $offer->effective_payout, 2) }}</td>
                                <td>
                                    <span class="bp-status-pill {{ $offer->has_offer ? 'bp-status-pill-active' : '' }}">
                                        {{ $offer->has_offer ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </td>
                            @endif

                            @if ($canManageOfferCaps)
                                <td>
                                    <label class="bp-toggle-inline" for="offer_cap_{{ $offer->idoffer }}">
                                        <input
                                            class="enable_offer_cap"
                                            type="checkbox"
                                            id="offer_cap_{{ $offer->idoffer }}"
                                            data-rep="{{ $offer->idrep }}"
                                            data-offer="{{ $offer->idoffer }}"
                                            name="enable_offer_cap"
                                            {{ $capEnabled }}
                                        >
                                        <span>Enable</span>
                                    </label>
                                </td>
                                <td>
                                    <input
                                        class="user_offer_cap bp-input-compact"
                                        type="number"
                                        step="1"
                                        min="0"
                                        data-rep="{{ $offer->idrep }}"
                                        data-offer="{{ $offer->idoffer }}"
                                        value="{{ (int) $offer->cap }}"
                                        name="user_offer_cap"
                                    >
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script type="text/javascript">
        (() => {
            const searchInput = document.getElementById('offerSearch');
            const rows = Array.from(document.querySelectorAll('#userOfferRows tr'));

            if (searchInput && rows.length) {
                searchInput.addEventListener('input', (event) => {
                    const query = event.target.value.trim().toLowerCase();

                    rows.forEach((row) => {
                        const haystack = row.getAttribute('data-search') || '';
                        row.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            $('#mainTable').tablesorter({
                sortList: [[0, 0]],
                widgets: ['staticRow']
            });

            document.querySelectorAll('.offer_access_check').forEach((checkbox) => {
                checkbox.addEventListener('change', (event) => {
                    const label = event.target.closest('.offer_access');
                    const text = label ? label.querySelector('span') : null;

                    if (text) {
                        text.textContent = event.target.checked ? 'Enabled' : 'Disabled';
                    }
                });
            });
        })();
    </script>
@endsection
