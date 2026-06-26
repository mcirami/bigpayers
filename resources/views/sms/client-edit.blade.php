@extends('layouts.dashboard-shell')

@section('page-title', 'Edit SMS Client')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">SMS Accounts</p>
                    <h2 class="bp-section-title value_span9">Edit SMS client</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Update the provider credentials saved for this SMS client record.
                    </p>
                </div>

                <a href="/sms" class="bp-button-secondary">Open SMS workspace</a>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(320px,0.95fr)]">
            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Credentials</p>
                    <h3 class="bp-section-title value_span9">Client login</h3>
                </div>

                <form action="/sms/client/update" method="post" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="id" value="{{ $smsClient->id }}">

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="client_id">Client ID</label>
                        <input value="{{ $smsClient->client_id }}" class="bp-form-input" type="text" name="client_id" id="client_id">
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="client_secret">Client Secret</label>
                        <input value="{{ $smsClient->client_secret }}" class="bp-form-input" type="text" name="client_secret" id="client_secret">
                    </div>

                    <div class="bp-form-field">
                        <label class="bp-form-label" for="sms_user_id">SMS User ID</label>
                        <input value="{{ $smsClient->sms_user_id }}" class="bp-form-input" type="text" name="sms_user_id" id="sms_user_id">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bp-button-primary">Update SMS client</button>
                    </div>
                </form>
            </section>

            <section class="bp-card value_span8">
                <div>
                    <p class="bp-section-kicker">Record</p>
                    <h3 class="bp-section-title value_span9">Current client</h3>
                </div>

                <div class="mt-6">
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">Record ID</span>
                        <span class="bp-detail-value">{{ $smsClient->id }}</span>
                    </div>
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">Client ID</span>
                        <span class="bp-detail-value">{{ $smsClient->client_id ?: 'Not set' }}</span>
                    </div>
                    <div class="bp-detail-row">
                        <span class="bp-detail-label">SMS User ID</span>
                        <span class="bp-detail-value">{{ $smsClient->sms_user_id ?: 'Not set' }}</span>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
