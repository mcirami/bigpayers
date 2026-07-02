@extends('layouts.dashboard-shell')

@section('page-title', 'Edit Affiliate Salaries')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Payroll Workspace</p>
                    <h2 class="bp-section-title value_span9">Edit affiliate salaries</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Create or update salary records for affiliates in your user tree.
                    </p>
                </div>

                <a href="/salaries" class="bp-button-secondary">Back to salaries</a>
            </div>
        </section>

        <section class="bp-card value_span8">
            <div class="bp-report-table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Affiliate</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Last Update</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($affiliates as $affiliate)
                        @php
                            $hasSalary = $affiliate['last_update'] !== null;
                            $lastUpdate = $hasSalary
                                ? \Carbon\Carbon::createFromFormat('U', $affiliate['last_update'])->diffForHumans()
                                : '';
                        @endphp
                        <tr>
                            <td>{{ $affiliate['user_name'] }}</td>
                            <td>{{ $affiliate['salary'] !== null ? '$' . number_format((float) $affiliate['salary'], 2) : '' }}</td>
                            <td>
                                @if((int) $affiliate['status'] === 1)
                                    <span class="text-green-700">Active</span>
                                @elseif($affiliate['status'] !== null && (int) $affiliate['status'] === 0)
                                    <span class="text-red-700">In-Active</span>
                                @else
                                    <span class="text-red-700">No Salary</span>
                                @endif
                            </td>
                            <td>{{ $lastUpdate }}</td>
                            <td>
                                <a class="bp-button-secondary" href="/user/{{ $affiliate['idrep'] }}/salary/{{ $hasSalary ? 'showUpdate' : 'show' }}">
                                    {{ $hasSalary ? 'Edit' : 'Create' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
