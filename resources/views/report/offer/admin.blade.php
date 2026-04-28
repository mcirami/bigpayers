@php
    use App\Privilege;
	use LeadMax\TrackYourStats\System\Session;
	$userType = Session::userType();
    $showRevenueColumns = in_array($userType, [Privilege::ROLE_GOD, Privilege::ROLE_MANAGER], true)
        || ($userType == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts"));
@endphp

@extends('report.template')

@section('report-title')
    Offer Reports
@endsection

@section('table-options')
    @include('report.options.dates')
    @if ($userType == 0 || $userType == 1)
        <div class="button_wrap" style="width: 100%; display:inline-block; margin-top: 10px;">
            <a class="bp-button-primary" href="/report/offer-data/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table class="table table-bordered table_01 tablesorter" id="mainTable">
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
			new LeadMax\TrackYourStats\Report\Formats\HTML(true,
			$array,$dates));
        @endphp

        </tbody>
    </table>
@endsection
@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[5, 1]],
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection
