@extends('layouts.dashboard-shell')

@section('page-title', 'Add SMS Client')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">SMS Accounts</p>
                    <h2 class="bp-section-title">Add SMS client</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Create a service phone number record for an affiliate while keeping the existing SMS client provisioning flow intact.
                    </p>
                </div>

                <a href="/sms" class="bp-button-secondary">Open SMS workspace</a>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(320px,0.95fr)]">
            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Create</p>
                    <h3 class="bp-section-title">Client details</h3>
                </div>

                <form action="/sms/client/create" method="post" class="mt-6 space-y-6">
                    @csrf

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="user_id">Affiliate</label>
                        <select class="bp-form-input" name="user_id" id="user_id">
                            @foreach($users as $user)
                                <option value="{{ $user->idrep }}">{{ $user->user_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="phoneNumber">Service Phone Number</label>
                        <input class="bp-form-input" type="text" name="phoneNumber" id="phoneNumber" placeholder="+1 (555) 555-5555">
                        <p class="bp-form-note">Include the country calling code at the front of the number.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary">Create SMS account</button>
                    </div>
                </form>
            </section>

            <section class="bp-card">
                <div>
                    <p class="bp-section-kicker">Access</p>
                    <h3 class="bp-section-title">Before creating</h3>
                </div>

                <div class="mt-6 bp-mini-list">
                    <div class="bp-link-card">
                        <p class="bp-link-label">Phone Format</p>
                        <p class="bp-link-value">Use international format, for example +1 (555) 555-5555.</p>
                    </div>

                    <div class="bp-link-card">
                        <p class="bp-link-label">Permission</p>
                        <p class="bp-link-value">The affiliate still needs SMS Chat permission before they can use the account.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
