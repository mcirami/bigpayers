@extends('layouts.dashboard-shell')

@section('page-title', 'IP Blacklist')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Security Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageHeading }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $introCopy }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/ip-blacklist" class="bp-button-secondary">Back to blacklist</a>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(320px,0.95fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Range Details</p>
                    <h3 class="bp-section-title value_span9">{{ $mode === 'edit' ? 'Update stored bounds' : 'Create blocked range' }}</h3>
                </div>

                <form method="post" action="{{ $formAction }}" class="mt-6 space-y-6">
                    @csrf

                    <div class="bp-form-grid">
                        <div class="bp-form-field">
                            <label class="bp-form-label" for="start">Start Range</label>
                            <input id="start" name="start" type="text" class="bp-form-input" value="{{ $values['start'] }}" placeholder="192.168.0.1" required>
                            <p class="bp-form-note">Enter the first IP address included in the blocked range.</p>
                        </div>

                        <div class="bp-form-field">
                            <label class="bp-form-label" for="end">End Range</label>
                            <input id="end" name="end" type="text" class="bp-form-input" value="{{ $values['end'] }}" placeholder="192.168.0.255" required>
                            <p class="bp-form-note">Use an equal or higher address than the start value.</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary">{{ $submitLabel }}</button>
                    </div>
                </form>
            </section>

            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Guidance</p>
                    <h3 class="bp-section-title value_span9">How range blocking works</h3>
                </div>

                <div class="mt-6 bp-mini-list">
                    <div class="bp-link-card">
                        <p class="bp-link-label">Accepted format</p>
                        <p class="bp-link-value">IPv4 start and end addresses like `10.0.0.1` through `10.0.0.255`.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">Evaluation</p>
                        <p class="bp-link-value">Incoming IPs are converted into numeric bounds and checked against each stored range.</p>
                    </div>

                    @if (!empty($entry))
                        <div class="bp-link-card">
                            <p class="bp-link-label">Created</p>
                            <p class="bp-link-value">{{ $entry->createdLabel }}</p>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
