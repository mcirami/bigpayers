@extends('layouts.dashboard-shell')

@push('head')
    @include('layouts.partials.report-head-assets')
@endpush

@push('scripts')
    @include('layouts.partials.report-script-assets')
@endpush

@php
    $filters = trim($__env->yieldContent('filters'));
    $rawReportTitle = trim($__env->yieldContent('report-title'));
    $plainReportTitle = trim(preg_replace('/\s+/', ' ', strip_tags($rawReportTitle)));
    $displayReportTitle = \Illuminate\Support\Str::limit($plainReportTitle, 72);
@endphp

@section('page-title', $displayReportTitle)

@section('content')

    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">Reporting Workspace</p>
                    <h2 class="bp-section-title value_span9" title="{{ $plainReportTitle }}">{{ $displayReportTitle }}</h2>
                </div>

                <div class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-500 shadow-sm">
                    Sort, filter, and export data
                </div>
            </div>

            <div class="mt-6 grid gap-4 {{ $filters !== '' ? 'xl:grid-cols-[minmax(0,1fr)_320px]' : '' }}">
                <div class="bp-report-toolbar">
                    @yield('table-options')
                </div>

                @if ($filters !== '')
                    <div class="bp-report-filter-panel">
                        {!! $filters !!}
                    </div>
                @endif
            </div>
        </section>

        <section class="bp-card value_span8">
            <div class="bp-report-table-wrap @if(Route::currentRouteName() == 'offerClicks' || Route::currentRouteName() == 'userClicks') adjust_overflow @endif">
                @yield('table')
            </div>
        </section>

        @yield('extra')
    </div>
@endsection
