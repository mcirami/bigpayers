@php
    use LeadMax\TrackYourStats\Report\Formats\HTML;
	use App\Privilege;
	use LeadMax\TrackYourStats\System\Session;
	$userType = Session::userType();
@endphp

@extends('report.template')

@section('report-title')
    Affiliate Reports
@endsection
<style>
    #loading_spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
    }

    #loading_spinner svg {
        width: 300px;
        height: 300px;
        color: #fff;
    }

</style>
@section('table-options')
    @include('report.options.user-type')
    @include('report.options.dates')
    @if ($userType == 0 || $userType == 1)
        <div class="button_wrap" style="width: 100%; display:inline-block; margin-top: 10px;">
            <a style="
			width: 170px;
			border:none;
			padding: 10px;
			font-size: 18px;
			border-radius: 6px;
			color: #676767;"
               class="btn btn-default btn-sm" href="/report/aff-data/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Rep ID</th>
            <th class="value_span9">Rep</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            @if(Session::userType() == Privilege::ROLE_GOD ||
                (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") ))
                <th class="value_span9  headers ">Sales Revenue</th>
            @endif
            @if(Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") && !Session::permissions()->can("view_sms_stats") )
                <th class="value_span9  ">Deductions</th>
            @endif
            @if(Session::userType() == Privilege::ROLE_GOD || Session::permissions()->can('view_sms_stats'))
                <th class="value_span9  ">Codes</th>
            @endif
            @if(Session::userType() == Privilege::ROLE_GOD ||
              (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") ))
                <th class="value_span9">EPC</th>
                <th class="value_span9">Bonus Revenue</th>
                <th class="value_span9">Referral Revenue</th>
                <th class="value_span9">TOTAL</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
            if (Session::userType() == Privilege::ROLE_GOD ||
            (Session::userType() == Privilege::ROLE_ADMIN && Session::permissions()->can("view_payouts") )) {
                    $array = [
                        'idrep',
                        'user_name',
                        'Clicks',
                        'UniqueClicks',
                        'FreeSignUps',
                        'PendingConversions',
                        'Conversions',
                        'Revenue',
                        Session::userType() == Privilege::ROLE_GOD || Session::permissions()->can('view_sms_stats') ? 'Codes' : 'Deductions',
                        'EPC',
                        'BonusRevenue',
                        'ReferralRevenue',
                        'TOTAL'
                    ];
                } else {
                    $array = [
                        'idrep',
                        'user_name',
                        'Clicks',
                        'UniqueClicks',
                        'FreeSignUps',
                        'PendingConversions',
                        'Conversions',
                    ];

					if (Session::userType() == Privilege::ROLE_GOD || Session::permissions()->can('view_sms_stats') ) {
					    $array[] = 'Codes';
					}
                }

                $reporter->between($dates['startDate'], $dates['endDate'],
                new HTML(true, $array));
        @endphp
        </tbody>
    </table>
@endsection
<div id="loading_spinner">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="3" r="0">
            <animate id="spinner_318l" begin="0;spinner_cvkU.end-0.5s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
        <circle cx="16.50" cy="4.21" r="0">
            <animate id="spinner_g5Gj" begin="spinner_318l.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
        <circle cx="7.50" cy="4.21" r="0"><animate id="spinner_cvkU" begin="spinner_Uuk0.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="19.79" cy="7.50" r="0"><animate id="spinner_e8rM" begin="spinner_g5Gj.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="4.21" cy="7.50" r="0"><animate id="spinner_Uuk0" begin="spinner_z7ol.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="21.00" cy="12.00" r="0">
            <animate id="spinner_MooL" begin="spinner_e8rM.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="3.00" cy="12.00" r="0"><animate id="spinner_z7ol" begin="spinner_KEoo.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="19.79" cy="16.50" r="0"><animate id="spinner_btyV" begin="spinner_MooL.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle><circle cx="4.21" cy="16.50" r="0">
            <animate id="spinner_KEoo" begin="spinner_1IYD.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="16.50" cy="19.79" r="0"><animate id="spinner_1sIS" begin="spinner_btyV.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="7.50" cy="19.79" r="0"><animate id="spinner_1IYD" begin="spinner_NWhh.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="12" cy="21" r="0">
            <animate id="spinner_NWhh" begin="spinner_1sIS.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
    </svg>
</div>

@section('footer')
    <script type="text/javascript">
		$(document).ready(function() {
			const loadClick = document.getElementsByClassName('load_click');
			for (const item of loadClick)
			{
				item.addEventListener('click', function() {
					document.getElementById('loading_spinner').style.display = 'flex';
				});
			}
			$("#mainTable").tablesorter(
				{
					sortList: [[7, 1]],
					widgets: ['staticRow']
				});
		});
    </script>
@endsection
