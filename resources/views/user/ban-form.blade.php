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
        $currentStatus = $ban ? ((int) $ban->status === 1 ? 'Active' : 'Inactive') : 'Ready';
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">{{ $pageTitle }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        {{ $isEdit
                            ? 'Update the expiration, status, or reason for this moderation record.'
                            : 'Create a moderation record and immediately disable access for this user.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/banned" class="bp-button-secondary">Back to banned users</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">{{ $affiliateTypeLabel }}</p>
                <p class="bp-stat-value">{{ $user->user_name }}</p>
                <p class="bp-stat-note">Account currently being moderated.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Status</p>
                <p class="bp-stat-value">{{ $currentStatus }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Current saved status for this ban record.' : 'A new ban will start as active.' }}</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Mode</p>
                <p class="bp-stat-value">{{ $isEdit ? 'Edit' : 'Create' }}</p>
                <p class="bp-stat-note">{{ $isEdit ? 'Updating an existing ban row.' : 'Creating a brand new ban row.' }}</p>
            </article>
        </section>

        <form action="{{ $formAction }}" method="post" class="space-y-6 lg:space-y-8">
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Ban Details</p>
                        <h3 class="bp-section-title value_span9">{{ $isEdit ? 'Update moderation settings' : 'Create moderation settings' }}</h3>
                    </div>
                    <p class="bp-table-meta">The reason is stored as entered and can be revised later from the banned users log.</p>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Expires</span>
                        <input
                            class="bp-form-input"
                            id="expires"
                            name="expires"
                            type="text"
                            value="{{ old('expires', optional($ban)->expires) }}"
                            required
                        >
                    </label>

                    @if($isEdit)
                        <label class="bp-form-field">
                            <span class="bp-form-label">Status</span>
                            <select class="bp-form-input" id="status" name="status">
                                <option value="1" {{ (int) old('status', optional($ban)->status) === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (int) old('status', optional($ban)->status) === 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </label>
                    @endif

                    <label class="bp-form-field bp-form-field-full">
                        <span class="bp-form-label">Reason</span>
                        <textarea class="bp-form-input bp-form-textarea" id="reason" name="reason">{{ old('reason', optional($ban)->reason) }}</textarea>
                    </label>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">
                    {{ $isEdit ? 'Save settings' : 'Ban user' }}
                </button>
                <a href="/user/banned" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#expires").datepicker({dateFormat: 'yy-mm-dd'});
        });
    </script>
@endsection
