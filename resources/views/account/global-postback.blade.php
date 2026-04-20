@extends('layouts.dashboard-shell')

@section('page-title', 'Global PostBack')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Tracking Workspace</p>
                    <h2 class="bp-section-title value_span9">Global conversion postback</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Set the fallback postback URL used whenever an offer-specific postback is not present. This keeps the original affiliate workflow intact while moving it into the redesigned shell.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="bp-link-card">
                        <p class="bp-link-label">Scope</p>
                        <p class="bp-link-value">Conversion fallback</p>
                    </article>
                    <article class="bp-link-card">
                        <p class="bp-link-label">Priority</p>
                        <p class="bp-link-value">Runs after offer-level overrides</p>
                    </article>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Postback URL</p>
                    <h3 class="bp-section-title value_span9">Fallback endpoint</h3>
                </div>

                <form method="post" action="/global-postback" class="mt-6 space-y-6">
                    @csrf

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="postback_url">On Conversion</label>
                        <input
                            id="postback_url"
                            name="postback_url"
                            type="text"
                            class="bp-form-input"
                            maxlength="255"
                            value="{{ $postbackUrl }}"
                            placeholder="https://example.com/postback?affid=#affid#&clickid=#clickid#"
                        >
                        <p class="bp-form-note">
                            If an offer does not define its own postback URL, this endpoint will automatically be used for conversions.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary">Save postback</button>
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
                            <span class="bp-token-pill">#offid#</span>
                            <span class="bp-token-pill">#clickid#</span>
                        </div>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">How substitution works</p>
                        <p class="bp-form-note">Variables are automatically injected when found in your URL.</p>
                        <p class="bp-form-note">Example: `domain.com/?var1=#affid#` becomes `domain.com/?var1=AffiliateID`.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">Additional sub variables</p>
                        <p class="bp-form-note">
                            You can pass `sub1` through `sub5` on the offer URL and reference them later in the postback.
                        </p>
                        <p class="bp-form-note">Example: `http://domain.com/?var1=#sub1#&var2=#sub2#`.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
