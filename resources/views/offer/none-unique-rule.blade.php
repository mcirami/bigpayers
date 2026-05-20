@extends('layouts.dashboard-shell')

@section('page-title', $mode === 'edit' ? 'Edit None-Unique Rule' : 'Create None-Unique Rule')

@section('content')
    @php
        $isEdit = $mode === 'edit';
        $selectedRedirectOffer = (int) old('redirect_offer', $rule->redirect_offer ?? 0);
        $selectedStatus = (int) old('is_active', $rule->is_active ?? 1);
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Rule Editor</p>
                    <h2 class="bp-section-title value_span9">{{ $isEdit ? 'Edit none-unique rule' : 'Create none-unique rule' }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Configure repeat-click traffic for {{ $offer->offer_name ?: 'Offer #' . $offer->idoffer }}.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/rules/{{ $offer->idoffer }}" class="bp-button-secondary">Back to rules</a>
                    <a href="/offer/view/{{ $offer->idoffer }}" class="bp-button-primary">View offer</a>
                </div>
            </div>
        </section>

        <section class="bp-card value_span8">
            <div>
                <p class="bp-section-kicker">None-Unique Rule</p>
                <h3 class="bp-section-title value_span9">Traffic redirect</h3>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ $action }}" class="mt-6">
                @csrf

                <div class="bp-form-grid">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Name</span>
                        <input
                            class="bp-form-input"
                            type="text"
                            name="name"
                            maxlength="255"
                            value="{{ old('name', $rule->name ?? '') }}"
                            required
                        >
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Redirect Offer</span>
                        <select class="bp-form-input" name="redirect_offer" required>
                            @foreach ($redirectOffers as $redirectOffer)
                                <option value="{{ $redirectOffer->idoffer }}" @if ((int) $redirectOffer->idoffer === $selectedRedirectOffer) selected @endif>
                                    {{ $redirectOffer->offer_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" name="is_active" required>
                            <option value="1" @if ($selectedStatus === 1) selected @endif>Active</option>
                            <option value="0" @if ($selectedStatus === 0) selected @endif>Inactive</option>
                        </select>
                    </label>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bp-button-primary">{{ $isEdit ? 'Save rule' : 'Create rule' }}</button>
                </div>
            </form>
        </section>
    </div>
@endsection
