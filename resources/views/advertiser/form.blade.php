@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', $pageTitle)

@section('content')
    @php
        $isEdit = $mode === 'edit';
        $assignedOffers = $campaign->relationLoaded('offers') ? $campaign->offers : collect();
        $offerCount = $assignedOffers->count();
        $createdAt = $isEdit && $campaign->timestamp
            ? \Carbon\Carbon::createFromTimestampUTC((int) $campaign->timestamp)->toFormattedDateString()
            : 'Will be set on save';
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Advertisers Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageTitle }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $isEdit
                            ? 'Update the advertiser name and review which offers are currently assigned to it.'
                            : 'Add a new advertiser record so offers can be grouped under the right owner.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/advertisers" class="bp-button-secondary">Back to advertisers</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Mode</p>
                <p class="bp-stat-value">{{ $isEdit ? 'Edit' : 'Create' }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Updating an existing advertiser record.' : 'Creating a new advertiser record.' }}</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assigned Offers</p>
                <p class="bp-stat-value">{{ $offerCount }}</p>
                <p class="bp-stat-note">Offers currently tied to this advertiser.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Created</p>
                <p class="bp-stat-value">{{ $createdAt }}</p>
                <p class="bp-stat-note">Creation date tracked for this advertiser record.</p>
            </article>
        </section>

        <form action="{{ $formAction }}" method="post" class="space-y-6 lg:space-y-8">
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Advertiser Details</p>
                        <h3 class="bp-section-title value_span9">{{ $isEdit ? 'Edit advertiser record' : 'Create advertiser record' }}</h3>
                    </div>
                    <p class="bp-table-meta">This name is what shows up in offer assignment and advertiser reporting.</p>
                </div>

                <div class="bp-form-grid mt-6">
                    <label class="bp-form-field bp-form-field-full">
                        <span class="bp-form-label">Name</span>
                        <input
                            class="bp-form-input"
                            id="campaign_name"
                            name="name"
                            type="text"
                            maxlength="155"
                            value="{{ old('name', $campaign->name ?? '') }}"
                            required
                        >
                    </label>
                </div>
            </section>

            @if($isEdit)
                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Assigned Offers</p>
                            <h3 class="bp-section-title value_span9">Current offer list</h3>
                        </div>
                        <p class="bp-table-meta">This is read-only for now and mirrors the legacy editor’s assigned offer view.</p>
                    </div>

                    <div class="mt-6">
                        @if($assignedOffers->isNotEmpty())
                            <div class="bp-checklist">
                                @foreach($assignedOffers as $offer)
                                    <div class="bp-checklist-item">
                                        <span>{{ $offer->offer_name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bp-inline-note">
                                <strong>No assigned offers</strong>
                                <span>This advertiser does not have any offers attached yet.</span>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">
                    {{ $isEdit ? 'Save advertiser' : 'Create advertiser' }}
                </button>
                <a href="/advertisers" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
