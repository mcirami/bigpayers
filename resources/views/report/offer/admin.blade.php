@php
    use App\Privilege;
    $showRevenueColumns = in_array($sessionUserType, [Privilege::ROLE_GOD, Privilege::ROLE_MANAGER], true)
        || ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts);
@endphp

@extends('report.template')

@section('report-title')
    Offer Reports
@endsection

@section('table-options')
    @include('report.options.dates')
    @if ($sessionUserType == Privilege::ROLE_GOD || $sessionUserType == Privilege::ROLE_ADMIN)
        <div class="bp-toolbar-actions">
            <a class="bp-button-primary" href="/report/offer-data/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table data-sort-default="5:desc" >
        <thead>

        <tr>
            <th class="value_span9">ID</th>
            <th class="value_span9">Offer</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
            @if ($showRevenueColumns)
                <th class="value_span9">Revenue</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
            if ($showRevenueColumns) {
				$array = ['idoffer', 'offer_name', 'Clicks', 'UniqueClicks', 'Conversions', 'Revenue'];
			} else {
				$array = ['idoffer', 'offer_name', 'Clicks', 'UniqueClicks', 'Conversions'];
			}

			$reporter->between($dates['startDate'], $dates['endDate'],
			new \App\Support\LegacyReportHtml(true,
			$array,$dates));
        @endphp

        </tbody>
    </table>
@endsection
