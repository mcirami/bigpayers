@extends('layouts.dashboard-shell')

@section('page-title', 'Affiliate Salaries')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Payroll Workspace</p>
                    <h2 class="bp-section-title">Affiliate salaries</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review active salary assignments and record this week's payouts.
                    </p>
                </div>

                @if($canEditSalaries)
                    <a href="/salaries/edit" class="bp-button-secondary">Edit salary setup</a>
                @endif
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="bp-stat-card">
                <p class="bp-stat-label">Active Salaries</p>
                <p class="bp-stat-value">{{ $affiliates->count() }}</p>
                <p class="bp-stat-note">Affiliates with active salary records.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Paid This Week</p>
                <p class="bp-stat-value">{{ $paidCount }}</p>
                <p class="bp-stat-note">Rows already logged for the current salary week.</p>
            </article>

            <article class="bp-stat-card">
                <p class="bp-stat-label">Unpaid</p>
                <p class="bp-stat-value">{{ $unpaidCount }}</p>
                <p class="bp-stat-note">Rows still eligible for payout entry.</p>
            </article>
        </section>

        <section class="bp-card">
            <form method="post" action="/salaries">
                @csrf

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Weekly Payroll</p>
                        <h3 class="bp-section-title">Salary payouts</h3>
                    </div>

                    @if($canPaySalaries)
                        <button type="submit" class="bp-button-primary">Pay all unpaid</button>
                    @endif
                </div>

                <div class="mt-6 bp-report-table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Affiliate</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Paid this Week</th>
                            <th>Reason</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($affiliates as $affiliate)
                            @php
                                $isPaid = $affiliate['payout'] !== null;
                                $lastUpdate = $affiliate['last_update'] !== null
                                    ? \Carbon\Carbon::createFromFormat('U', $affiliate['last_update'])->diffForHumans()
                                    : '';
                            @endphp
                            <tr>
                                <td>{{ $affiliate['user_name'] }}</td>
                                <td>${{ number_format((float) $affiliate['salary'], 2) }}</td>
                                <td><span class="{{ $isPaid ? 'text-green-700' : 'text-red-700' }}">{{ $isPaid ? 'PAID' : 'UN-PAID' }}</span></td>
                                <td>
                                    @if($isPaid || !$canEditSalaries)
                                        {{ $affiliate['payout'] }}
                                    @else
                                        <input type="hidden" name="salaries[{{ $affiliate['idrep'] }}][salary_id]" value="{{ $affiliate['id'] }}">
                                        <input class="bp-form-input" type="number" step="0.01" name="salaries[{{ $affiliate['idrep'] }}][payout]" value="{{ $affiliate['salary'] }}">
                                    @endif
                                </td>
                                <td>
                                    @if($isPaid || !$canEditSalaries)
                                        {{ $affiliate['reason'] }}
                                    @else
                                        <input class="bp-form-input" type="text" name="salaries[{{ $affiliate['idrep'] }}][reason]" value="">
                                    @endif
                                </td>
                                <td>{{ $lastUpdate }}</td>
                                <td>
                                    @if(!$isPaid && $canPaySalaries)
                                        <button type="submit" name="pay_user" value="{{ $affiliate['idrep'] }}" class="bp-button-secondary">Pay</button>
                                    @endif
                                    @if($canEditSalaries)
                                        <a class="bp-button-secondary" href="/user/{{ $affiliate['idrep'] }}/salary/showUpdate">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </section>
    </div>
@endsection
