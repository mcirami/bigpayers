@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', 'Activate User')

@section('content')
    @php
        $hasReferralAccess = \LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_REFERRALS);
        $requestedAt = $user->rep_timestamp ? \Carbon\Carbon::parse($user->rep_timestamp)->toFormattedDateString() : 'Unknown';
    @endphp

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Users Workspace</p>
                    <h2 class="bp-section-title value_span9">Activate {{ $affiliateTypeLabel }}</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Confirm the signup details below, choose the owning {{ strtolower($accountTypeLabel) }}, and activate the account into the live tree.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="/user/pending" class="bp-button-secondary">Back to pending</a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">{{ $affiliateTypeLabel }}</p>
                <p class="bp-stat-value">{{ $user->user_name }}</p>
                <p class="bp-stat-note">Username that will become active after approval.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Requested</p>
                <p class="bp-stat-value">{{ $requestedAt }}</p>
                <p class="bp-stat-note">Original signup request date.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Assignable {{ $accountTypeLabelPlural }}</p>
                <p class="bp-stat-value">{{ $assignableManagers->count() }}</p>
                <p class="bp-stat-note">Available {{ strtolower($accountTypeLabelPlural) }} this account can be assigned to.</p>
            </article>
        </section>

        <form action="/user/pending/{{ $user->idrep }}/activate" method="post" class="space-y-6 lg:space-y-8">
            {{ csrf_field() }}

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Signup Details</p>
                        <h3 class="bp-section-title value_span9">Review submitted information</h3>
                    </div>
                    <p class="bp-table-meta">These fields are read-only here so approval stays focused on assignment.</p>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">First Name</span>
                        <input class="bp-form-input" type="text" value="{{ $user->first_name }}" readonly>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Last Name</span>
                        <input class="bp-form-input" type="text" value="{{ $user->last_name }}" readonly>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Email</span>
                        <input class="bp-form-input" type="text" value="{{ $user->email }}" readonly>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Username</span>
                        <input class="bp-form-input" type="text" value="{{ $user->user_name }}" readonly>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Company</span>
                        <input class="bp-form-input" type="text" value="{{ $user->company_name }}" readonly>
                    </label>

                    <label class="bp-form-field">
                        <span class="bp-form-label">Telegram</span>
                        <input class="bp-form-input" type="text" value="{{ $user->skype }}" readonly>
                    </label>
                </div>
            </section>

            <section class="bp-card value_span8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Assignment</p>
                        <h3 class="bp-section-title value_span9">Choose the owning {{ strtolower($accountTypeLabel) }}</h3>
                    </div>
                    <p class="bp-table-meta">Activation will also assign all current public offers to the new account.</p>
                </div>

                <div class="bp-form-grid mt-6 md:grid-cols-2">
                    <label class="bp-form-field">
                        <span class="bp-form-label">Assign To</span>
                        <select class="bp-form-input" id="referrer_repid" name="referrer_repid" required>
                            <option value="">Select {{ strtolower($accountTypeLabel) }}</option>
                            @foreach($assignableManagers as $manager)
                                <option value="{{ $manager->idrep }}">{{ $manager->user_name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            @if($hasReferralAccess)
                <section class="bp-card value_span8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="bp-section-kicker">Referral Settings</p>
                            <h3 class="bp-section-title value_span9">Optional referral payout</h3>
                        </div>
                        <p class="bp-table-meta">Leave this collapsed to activate without a referral setup.</p>
                    </div>

                    <div class="mt-6 space-y-5">
                        <label class="bp-choice-pill">
                            <input type="checkbox" id="enable_referral" name="enable_referral" value="1">
                            <span>Enable referral for this {{ strtolower($affiliateTypeLabel) }}</span>
                        </label>

                        <div class="bp-form-grid md:grid-cols-2" id="referral_fields" style="display:none;">
                            <label class="bp-form-field">
                                <span class="bp-form-label">Referrer</span>
                                <select class="bp-form-input" id="referral_user_id" name="referral_user_id" disabled>
                                    <option value="">Select affiliate</option>
                                    @foreach($referralOptions as $referralUser)
                                        <option value="{{ $referralUser->idrep }}">{{ $referralUser->user_name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Type</span>
                                <select class="bp-form-input" id="referral_type" name="referral_type" disabled>
                                    <option value="flat">Flat Fee</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">Start Date</span>
                                <input class="bp-form-input" id="start_date" name="start_date" type="text" disabled>
                            </label>

                            <label class="bp-form-field">
                                <span class="bp-form-label">End Date</span>
                                <input class="bp-form-input" id="end_date" name="end_date" type="text" disabled>
                            </label>

                            <label class="bp-form-field bp-form-field-full">
                                <span class="bp-form-label">Amount</span>
                                <input class="bp-form-input" id="amount" name="amount" type="number" min="0" step="0.01" value="0" disabled>
                            </label>
                        </div>
                    </div>
                </section>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="bp-button-primary value_span6-2 value_span2 value_span1-2">Activate {{ $affiliateTypeLabel }}</button>
                <a href="/user/pending" class="bp-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#start_date, #end_date").datepicker({dateFormat: 'yy-mm-dd'});

            $("#enable_referral").on("change", function () {
                const enabled = $(this).is(":checked");
                $("#referral_fields").stop(true, true)[enabled ? "slideDown" : "slideUp"]("fast");
                $("#referral_user_id, #referral_type, #start_date, #end_date, #amount").prop("disabled", !enabled);
            });
        });
    </script>
@endsection
