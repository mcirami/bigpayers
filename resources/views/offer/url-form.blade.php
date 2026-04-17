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
        $activeCount = $activeUrls ?? null;
        $serverIp = request()->server('SERVER_ADDR') ?: request()->ip();
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Offers Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageTitle }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $isEdit
                            ? 'Update the branded domain settings for this offer URL without leaving the new shell.'
                            : 'Add a branded domain for outbound offer links and keep the URL registry current.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/offer/urls" class="bp-button-secondary">Back to offer URLs</a>
                    @if($isEdit)
                        <span class="bp-status-pill {{ (int) $offerUrl->status === 1 ? 'bp-status-pill-active' : '' }}">
                            {{ (int) $offerUrl->status === 1 ? 'Active' : 'Inactive' }}
                        </span>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 {{ $isEdit ? 'xl:grid-cols-3' : 'xl:grid-cols-2' }}">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Server Target</p>
                <p class="bp-stat-value">{{ $serverIp }}</p>
                <p class="bp-stat-note">Point the domain to this IP before using it in active offer links.</p>
            </article>

            @if($isEdit)
                <article class="bp-stat-card">
                    <p class="bp-stat-label">Added</p>
                    <p class="bp-stat-value">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $offerUrl->timestamp)->toFormattedDateString() }}</p>
                    <p class="bp-stat-note">Original creation date for this URL record.</p>
                </article>
            @endif

            <article class="bp-stat-card">
                <p class="bp-stat-label">Active URLs</p>
                <p class="bp-stat-value">{{ $activeCount }}</p>
                <p class="bp-stat-note">Current active branded domains available in the offer flow.</p>
            </article>
        </section>

        <form action="{{ $formAction }}" method="post" class="space-y-6 lg:space-y-8">
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">URL Details</p>
                        <h3 class="bp-section-title value_span9">{{ $isEdit ? 'Edit branded domain' : 'Add branded domain' }}</h3>
                    </div>
                    <p class="bp-table-meta">These settings control whether the URL can appear as an outbound option in the offers workflow.</p>
                </div>

                <div class="bp-form-grid mt-6">
                    <label class="bp-form-field bp-form-field-full">
                        <span class="bp-form-label">URL</span>
                        <input
                            class="bp-form-input"
                            type="text"
                            name="url"
                            value="{{ old('url', $offerUrl->url ?? '') }}"
                            placeholder="example.yourdomain.com"
                            required
                        >
                        <span class="bp-form-note">Enter the domain or offer URL host exactly as it should appear in the offer selector.</span>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Status</span>
                        <select class="bp-form-input" name="status">
                            <option value="1" @selected((int) old('status', $offerUrl->status ?? 1) === 1)>Active</option>
                            <option value="0" @selected((int) old('status', $offerUrl->status ?? 1) === 0)>Inactive</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">
                    {{ $isEdit ? 'Save changes' : 'Create URL' }}
                </button>
                <a href="/offer/urls" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
