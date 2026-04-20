@php
    $oldDate = old('date');
    $formattedOldDate = null;

    if ($oldDate) {
        try {
            $formattedOldDate = \Illuminate\Support\Carbon::parse($oldDate, 'UTC')->format('Y-m-d\TH:i');
        } catch (\Throwable $exception) {
            $formattedOldDate = null;
        }
    }
@endphp

@extends('layouts.dashboard-shell')

@section('page-title', 'Add Sale')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Finance Workspace</p>
                    <h2 class="bp-section-title value_span9">Manual sale entry</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Create a generated click and conversion pair for an existing {{ strtolower($affiliateLabel) }} and offer, then log the adjustment back into the existing adjustments report.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Flow</p>
                        <p class="bp-link-value">Generated click + conversion</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Timestamp</p>
                        <p class="bp-link-value">Saved in UTC</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Redirect</p>
                        <p class="bp-link-value">Adjustments report</p>
                    </article>
                </div>
            </div>
        </section>

        <form action="/sales/add" method="post" id="sale-form" class="space-y-6 lg:space-y-8">
            @csrf

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Selection</p>
                        <h3 class="bp-section-title value_span9">Choose {{ strtolower($affiliateLabel) }} and offer</h3>
                    </div>

                    <div class="mt-6 bp-selection-grid bp-sale-selection-grid">
                        <div class="bp-selection-card">
                            <p class="bp-detail-label">{{ $affiliatePluralLabel }}</p>
                            <h4 class="bp-selection-title value_span9">Active {{ strtolower($affiliatePluralLabel) }}</h4>
                            <div class="mt-4 space-y-3">
                                <input
                                    type="text"
                                    id="affiliate-search"
                                    class="bp-form-input"
                                    placeholder="Search {{ strtolower($affiliatePluralLabel) }} by name or ID..."
                                    autocomplete="off"
                                >
                                <select name="affiliate" id="affiliate-select" class="bp-dual-select" size="10" required></select>
                                <p class="bp-form-note" id="affiliate-note">Loading active {{ strtolower($affiliatePluralLabel) }}...</p>
                            </div>
                        </div>

                        <div class="bp-selection-card">
                            <p class="bp-detail-label">Offers</p>
                            <h4 class="bp-selection-title value_span9">Available offers</h4>
                            <div class="mt-4 space-y-3">
                                <input
                                    type="text"
                                    id="offer-search"
                                    class="bp-form-input"
                                    placeholder="Search offers by name or ID..."
                                    autocomplete="off"
                                >
                                <select name="offer" id="offer-select" class="bp-dual-select" size="10" required disabled></select>
                                <p class="bp-form-note" id="offer-note">Choose a {{ strtolower($affiliateLabel) }} first to load their active offers.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bp-card value_span8">
                    <div>
                        <p class="bp-section-kicker">Sale Details</p>
                        <h3 class="bp-section-title value_span9">Timestamp and payout</h3>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div class="bp-form-field">
                            <label class="bp-form-label" for="date-display">Date</label>
                            <input
                                id="date-display"
                                type="datetime-local"
                                class="bp-form-input"
                                value="{{ $formattedOldDate ?: $defaultTimestamp }}"
                                required
                            >
                            <input id="date" name="date" type="hidden" value="{{ old('date', now('UTC')->format('Y-m-d H:i:s')) }}">
                            <p class="bp-form-note">Timestamps are stored in UTC in the underlying click and conversion records.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="custom-payout">
                                <input type="checkbox" id="custom-payout-enabled" class="mr-2" {{ old('customPayout') !== null ? 'checked' : '' }}>
                                Custom payout
                            </label>
                            <input
                                id="custom-payout"
                                name="customPayout"
                                type="number"
                                step="0.10"
                                min="0"
                                class="bp-form-input"
                                value="{{ old('customPayout', '0.00') }}"
                                {{ old('customPayout') !== null ? '' : 'disabled' }}
                            >
                            <p class="bp-form-note">Leave this off to use the standard payout from the existing {{ strtolower($affiliateLabel) }} offer assignment.</p>
                        </div>

                        <div class="bp-mini-list">
                            <div class="bp-link-card">
                                <p class="bp-link-label">Selected {{ $affiliateLabel }}</p>
                                <p class="bp-link-value" id="selected-affiliate-preview">No {{ strtolower($affiliateLabel) }} selected yet.</p>
                            </div>

                            <div class="bp-link-card">
                                <p class="bp-link-label">Selected Offer</p>
                                <p class="bp-link-value" id="selected-offer-preview">No offer selected yet.</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bp-button-primary">Create sale</button>
                        </div>
                    </div>
                </section>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script>
        (() => {
            const affiliateLabel = @json($affiliateLabel);
            const oldAffiliateId = @json((string) old('affiliate', ''));
            const oldOfferId = @json((string) old('offer', ''));

            const affiliateSearch = document.getElementById('affiliate-search');
            const offerSearch = document.getElementById('offer-search');
            const affiliateSelect = document.getElementById('affiliate-select');
            const offerSelect = document.getElementById('offer-select');
            const affiliateNote = document.getElementById('affiliate-note');
            const offerNote = document.getElementById('offer-note');
            const selectedAffiliatePreview = document.getElementById('selected-affiliate-preview');
            const selectedOfferPreview = document.getElementById('selected-offer-preview');
            const customPayoutEnabled = document.getElementById('custom-payout-enabled');
            const customPayoutInput = document.getElementById('custom-payout');
            const dateDisplay = document.getElementById('date-display');
            const hiddenDate = document.getElementById('date');
            const saleForm = document.getElementById('sale-form');

            let affiliates = [];
            let offers = [];

            const sortByName = (items) => [...items].sort((a, b) => a.name.localeCompare(b.name));

            const updateHiddenDate = () => {
                if (!dateDisplay.value) {
                    hiddenDate.value = '';
                    return;
                }

                hiddenDate.value = `${dateDisplay.value.replace('T', ' ')}:00`;
            };

            const renderOptions = (select, items, selectedId) => {
                select.innerHTML = '';

                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.name} - ${item.id}`;

                    if (String(item.id) === String(selectedId)) {
                        option.selected = true;
                    }

                    select.appendChild(option);
                });
            };

            const filterItems = (items, searchTerm) => {
                const needle = searchTerm.trim().toLowerCase();

                if (!needle) {
                    return sortByName(items);
                }

                return sortByName(items.filter((item) => {
                    return item.name.toLowerCase().includes(needle) || String(item.id).includes(needle);
                }));
            };

            const updateAffiliatePreview = () => {
                const selected = affiliates.find((item) => String(item.id) === affiliateSelect.value);
                selectedAffiliatePreview.textContent = selected
                    ? `${selected.name} - ${selected.id}`
                    : `No ${affiliateLabel.toLowerCase()} selected yet.`;
            };

            const updateOfferPreview = () => {
                const selected = offers.find((item) => String(item.id) === offerSelect.value);
                selectedOfferPreview.textContent = selected
                    ? `${selected.name} - ${selected.id}`
                    : 'No offer selected yet.';
            };

            const refreshAffiliateOptions = () => {
                const filtered = filterItems(affiliates, affiliateSearch.value);
                const currentSelection = affiliateSelect.value || oldAffiliateId;
                renderOptions(affiliateSelect, filtered, currentSelection);
                updateAffiliatePreview();
            };

            const refreshOfferOptions = () => {
                const filtered = filterItems(offers, offerSearch.value);
                const currentSelection = offerSelect.value || oldOfferId;
                renderOptions(offerSelect, filtered, currentSelection);
                updateOfferPreview();
            };

            const loadOffers = async (affiliateId, preferredOfferId = '') => {
                offers = [];
                offerSelect.disabled = true;
                offerNote.textContent = 'Loading offers for the selected affiliate...';
                refreshOfferOptions();

                if (!affiliateId) {
                    offerNote.textContent = 'Choose a ' + affiliateLabel.toLowerCase() + ' first to load their active offers.';
                    return;
                }

                try {
                    const response = await fetch(`/sales/affiliate-offers/${affiliateId}`, {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load offers.');
                    }

                    offers = await response.json();
                    offerSelect.disabled = offers.length === 0;
                    refreshOfferOptions();

                    if (preferredOfferId) {
                        offerSelect.value = preferredOfferId;
                    }

                    updateOfferPreview();
                    offerNote.textContent = offers.length
                        ? `${offers.length} active offers available for the selected ${affiliateLabel.toLowerCase()}.`
                        : `No active offers were found for this ${affiliateLabel.toLowerCase()}.`;
                } catch (error) {
                    offerSelect.disabled = true;
                    offerNote.textContent = 'Unable to load offers right now.';
                    selectedOfferPreview.textContent = 'Offer list failed to load.';
                }
            };

            const syncCustomPayoutState = () => {
                customPayoutInput.disabled = !customPayoutEnabled.checked;
            };

            affiliateSearch.addEventListener('input', refreshAffiliateOptions);
            offerSearch.addEventListener('input', refreshOfferOptions);

            affiliateSelect.addEventListener('change', () => {
                updateAffiliatePreview();
                loadOffers(affiliateSelect.value);
            });

            offerSelect.addEventListener('change', updateOfferPreview);
            customPayoutEnabled.addEventListener('change', syncCustomPayoutState);
            dateDisplay.addEventListener('change', updateHiddenDate);
            saleForm.addEventListener('submit', updateHiddenDate);

            const init = async () => {
                updateHiddenDate();
                syncCustomPayoutState();

                try {
                    const response = await fetch('/sales/affiliates', {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load affiliates.');
                    }

                    affiliates = await response.json();
                    refreshAffiliateOptions();

                    const initialAffiliateId = oldAffiliateId || affiliateSelect.value;

                    if (initialAffiliateId) {
                        affiliateSelect.value = initialAffiliateId;
                        updateAffiliatePreview();
                        await loadOffers(initialAffiliateId, oldOfferId);
                    } else {
                        affiliateNote.textContent = `Choose from ${affiliates.length} active ${affiliateLabel.toLowerCase()} accounts.`;
                    }
                } catch (error) {
                    affiliateNote.textContent = 'Unable to load affiliates right now.';
                    selectedAffiliatePreview.textContent = `${affiliateLabel} list failed to load.`;
                }
            };

            init();
        })();
    </script>
@endsection
