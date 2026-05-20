@extends('layouts.dashboard-shell')

@section('page-title', 'Offer PostBack')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Tracking Workspace</p>
                    <h2 class="bp-section-title value_span9">Offer postbacks for {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Set offer-level postback URLs for conversion, free signup, and deduction events. These values override your global postback when they are present.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/manage" class="bp-button-secondary">Back to offers</a>
                    <a href="/global-postback" class="bp-button-primary">Global postback</a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Offer-Level URLs</p>
                    <h3 class="bp-section-title value_span9">Event endpoints</h3>
                </div>

                <form method="post" action="/offer/{{ $offer->idoffer }}/postback" class="mt-6 space-y-6">
                    @csrf

                    <label class="bp-form-field" for="postback_url">
                        <span class="bp-form-label">Conversion PostBack URL</span>
                        <input
                            id="postback_url"
                            name="postback_url"
                            type="text"
                            class="bp-form-input"
                            maxlength="255"
                            value="{{ $conversionPostback }}"
                            placeholder="https://example.com/conversion?affid=#affid#&clickid=#clickid#"
                        >
                        <span class="bp-form-note">Fires when a conversion is registered for this offer.</span>
                    </label>

                    <label class="bp-form-field" for="free_sign_up_url">
                        <span class="bp-form-label">Free Sign Up PostBack URL</span>
                        <input
                            id="free_sign_up_url"
                            name="free_sign_up_url"
                            type="text"
                            class="bp-form-input"
                            maxlength="255"
                            value="{{ $freeSignUpPostback }}"
                            placeholder="https://example.com/signup?function=free&clickid=#clickid#"
                        >
                        <span class="bp-form-note">Fires for free signup events.</span>
                    </label>

                    <label class="bp-form-field" for="deduction_url">
                        <span class="bp-form-label">Deduction PostBack URL</span>
                        <input
                            id="deduction_url"
                            name="deduction_url"
                            type="text"
                            class="bp-form-input"
                            maxlength="255"
                            value="{{ $deductionPostback }}"
                            placeholder="https://example.com/deduct?function=deduct&clickid=#clickid#"
                        >
                        <span class="bp-form-note">Fires when a deduction event is registered.</span>
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary">Save postbacks</button>
                    </div>
                </form>
            </section>

            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Variable Reference</p>
                    <h3 class="bp-section-title value_span9">Supported tokens</h3>
                </div>

                <div class="mt-6 bp-mini-list">
                    <div class="bp-link-card">
                        <p class="bp-link-label">Core variables</p>
                        <div class="bp-token-list">
                            <span class="bp-token-pill">#affid#</span>
                            <span class="bp-token-pill">#user#</span>
                            <span class="bp-token-pill">#offid#</span>
                            <span class="bp-token-pill">#clickid#</span>
                        </div>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">Sub variables</p>
                        <p class="bp-form-note">
                            Append `sub1` through `sub5` to the offer URL, then reference them later with `#sub1#`, `#sub2#`, and so on.
                        </p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">URL format</p>
                        <p class="bp-form-note">
                            When adding custom values to an existing query string, separate variables with an ampersand.
                        </p>
                        <p class="bp-form-note">Example: `https://domain.com/?var1=#sub1#&var2=#sub2#`.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
