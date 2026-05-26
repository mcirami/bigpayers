@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@section('page-title', $pageTitle)

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Reports Workspace</p>
                    <h2 class="bp-section-title value_span9">Affiliate report permissions</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Choose which report columns each affiliate can see in their affiliate reports.
                    </p>
                </div>
            </div>
        </section>

        <section class="bp-card value_span8">
            <form method="post" action="/admin/report-permissions">
                @csrf

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Column Access</p>
                        <h3 class="bp-section-title value_span9">Report visibility</h3>
                    </div>

                    <button type="submit" class="bp-button-primary">Save permissions</button>
                </div>

                <div class="mt-6 bp-report-table-wrap">
                    <table class="table table-bordered table_01 table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Name</th>
                            @foreach($permissionColumns as $column)
                                <th>{{ \Illuminate\Support\Str::headline($column) }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($affiliates as $affiliate)
                            <tr>
                                <td>{{ $affiliate->user_id }}</td>
                                <td>{{ $affiliate->user_name }}</td>
                                @foreach($permissionColumns as $column)
                                    <td>
                                        <input type="hidden" name="permissions[{{ $affiliate->user_id }}][{{ $column }}]" value="0">
                                        <input
                                            class="fixCheckBox"
                                            type="checkbox"
                                            name="permissions[{{ $affiliate->user_id }}][{{ $column }}]"
                                            value="1"
                                            @checked((int) ($affiliate->{$column} ?? 0) === 1)
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($permissionColumns) + 2 }}">No owned affiliates were found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </section>
    </div>
@endsection
