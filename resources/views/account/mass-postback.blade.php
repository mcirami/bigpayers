@extends('layouts.dashboard-shell')

@section('page-title', 'Mass Postback Assignment')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Tracking Workspace</p>
                    <h2 class="bp-section-title value_span9">Mass assign postback URL</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Apply one conversion postback URL to multiple offers you already have access to.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/global-postback" class="bp-button-secondary">Global postback</a>
                    <a href="/dashboard" class="bp-button-primary">Dashboard</a>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('message'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Postback URL</p>
                    <h3 class="bp-section-title value_span9">Conversion endpoint</h3>
                </div>

                <form method="post" action="/account/mass-postback" class="mt-6 space-y-6">
                    @csrf

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="postback_url">Postback URL</label>
                        <input
                            id="postback_url"
                            name="postback_url"
                            type="text"
                            class="bp-form-input"
                            maxlength="255"
                            value="{{ old('postback_url') }}"
                            placeholder="https://example.com/postback?affid=#affid#&clickid=#clickid#"
                        >
                    </div>

                    <div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="bp-section-kicker">Offers</p>
                                <h3 class="bp-section-title value_span9">Select offers</h3>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="bp-button-secondary" onclick="setMassPostbackOffers(true)">Check all</button>
                                <button type="button" class="bp-button-secondary" onclick="setMassPostbackOffers(false)">Uncheck all</button>
                            </div>
                        </div>

                        @if($offers->count() > 0)
                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                @foreach($offers as $offer)
                                    <label class="bp-link-card flex cursor-pointer items-center gap-3">
                                        <input
                                            type="checkbox"
                                            class="mass-postback-offer"
                                            name="offerList[]"
                                            value="{{ $offer->idoffer }}"
                                            {{ in_array((string) $offer->idoffer, old('offerList', []), true) ? 'checked' : '' }}
                                        >
                                        <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-700">
                                            {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-5 bp-inline-note">
                                <strong>No offers available</strong>
                                <span>You do not currently have assigned offers to update.</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary" {{ $offers->isEmpty() ? 'disabled' : '' }}>Assign URL</button>
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
                        <div class="bp-token-list">
                            <span class="bp-token-pill">#sub1#</span>
                            <span class="bp-token-pill">#sub2#</span>
                            <span class="bp-token-pill">#sub3#</span>
                            <span class="bp-token-pill">#sub4#</span>
                            <span class="bp-token-pill">#sub5#</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        function setMassPostbackOffers(checked) {
            document.querySelectorAll('.mass-postback-offer').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        }
    </script>
@endsection
