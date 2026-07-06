@extends('layouts.dashboard-shell')

@section('page-title', 'User Offers')

@section('content')
    @php
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
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title">{{ $name }}'s offer access</h2>
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
                        Review offer access, update payout values, and manage account-specific offer settings from this workspace.
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

        <section class="bp-card">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="bp-section-kicker">Offer Controls</p>
                    <h3 class="bp-section-title">User offer matrix</h3>
                </div>
                <p class="bp-table-meta">Payout changes save on enter or blur, and access toggles update immediately.</p>
            </div>

            <div class="mt-6 bp-report-table-wrap">
                <table class="bp-user-offers-table" id="mainTable" data-sortable-table data-sort-default="0:asc">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Payout</th>
                        <th>Custom $</th>
                        <th>Access</th>
                        @if ($canManageOfferCaps)
                            <th>Cap</th>
                            <th>Daily Max</th>
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
    <script type="text/javascript">
        (() => {
            const csrfToken = @json(csrf_token());
            const searchInput = document.getElementById('offerSearch');
            const rows = Array.from(document.querySelectorAll('#userOfferRows tr'));
            const errorBanner = document.getElementById('error_message');
            const errorText = errorBanner ? errorBanner.querySelector('p') : null;

            const showError = (message) => {
                if (!errorBanner || !errorText) {
                    return;
                }

                errorText.textContent = message || 'Unable to save this change.';
                errorBanner.classList.add('active');

                setTimeout(() => {
                    errorBanner.classList.remove('active');
                }, 5000);
            };

            const postJson = async (url, packets) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(packets)
                });

                if (!response.ok) {
                    throw new Error('Unable to save this change.');
                }

                return response.json();
            };

            const saveOnEnterOrBlur = (input, callback) => {
                ['keydown', 'focusout'].forEach((eventName) => {
                    input.addEventListener(eventName, (event) => {
                        if (eventName === 'keydown' && event.key !== 'Enter') {
                            return;
                        }

                        callback(event);
                    });
                });
            };

            if (searchInput && rows.length) {
                searchInput.addEventListener('input', (event) => {
                    const query = event.target.value.trim().toLowerCase();

                    rows.forEach((row) => {
                        const haystack = row.getAttribute('data-search') || '';
                        row.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            document.querySelectorAll('.update_aff_payout').forEach((input) => {
                saveOnEnterOrBlur(input, async (event) => {
                    const target = event.target;
                    const payout = target.value.trim();

                    try {
                        const data = await postJson('/user/change-aff-payout', {
                            payout: payout,
                            offer_id: target.dataset.offer,
                            rep: target.dataset.rep
                        });

                        if (!data.success) {
                            showError(data.message);
                            return;
                        }

                        const field = target.closest('.bp-custom-payout-field');
                        const hint = field ? field.querySelector('.bp-custom-payout-hint') : null;
                        const fallbackDisplay = target.dataset.fallbackDisplay || 'default payout';
                        const hasCustomPayout = payout !== '';

                        if (field) {
                            field.classList.toggle('is-custom', hasCustomPayout);
                            field.classList.toggle('is-fallback', !hasCustomPayout);
                        }

                        if (hint) {
                            hint.textContent = hasCustomPayout
                                ? 'Custom override active'
                                : `Using fallback: ${fallbackDisplay}`;
                        }

                        target.classList.add('updated_animation');
                        setTimeout(() => {
                            target.classList.remove('updated_animation');
                        }, 3000);
                    } catch (error) {
                        showError(error.message);
                    }
                });
            });

            document.querySelectorAll('.offer_access_check').forEach((checkbox) => {
                checkbox.addEventListener('change', async (event) => {
                    const target = event.target;
                    const label = target.closest('.offer_access');
                    const text = label ? label.querySelector('span') : null;
                    const offerId = target.dataset.offer;

                    if (text) {
                        text.textContent = target.checked ? 'Enabled' : 'Disabled';
                    }

                    const packets = {
                        access: target.checked,
                        rep: target.dataset.rep,
                        offer_id: offerId
                    };
                    const payoutInput = document.getElementById('offer_' + offerId);

                    if (target.checked && payoutInput) {
                        packets.payout = payoutInput.value;
                    }

                    try {
                        const data = await postJson('/user/update-offer-access', packets);

                        if (!data.success) {
                            showError(data.message);
                        }
                    } catch (error) {
                        showError(error.message);
                    }
                });
            });

            document.querySelectorAll('.enable_offer_cap').forEach((checkbox) => {
                checkbox.addEventListener('change', async (event) => {
                    const target = event.target;

                    try {
                        const data = await postJson('/user/enable-user-offer-cap', {
                            offer_id: target.dataset.offer,
                            rep: target.dataset.rep,
                            status: target.checked
                        });

                        if (!data.success) {
                            showError(data.message);
                        }
                    } catch (error) {
                        showError(error.message);
                    }
                });
            });

            document.querySelectorAll('.user_offer_cap').forEach((input) => {
                saveOnEnterOrBlur(input, async (event) => {
                    const target = event.target;

                    try {
                        const data = await postJson('/user/set-user-offer-cap', {
                            offer_id: target.dataset.offer,
                            rep: target.dataset.rep,
                            cap: target.value
                        });

                        if (!data.success) {
                            showError(data.message);
                            return;
                        }

                        target.classList.add('updated_animation');
                        setTimeout(() => {
                            target.classList.remove('updated_animation');
                        }, 3000);
                    } catch (error) {
                        showError(error.message);
                    }
                });
            });
        })();
    </script>
    @include('layouts.partials.sortable-table-script')
@endsection
