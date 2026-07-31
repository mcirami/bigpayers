@php
    use App\Support\Report\Formats\Html;
	use App\Privilege;
    $showRevenueColumns = in_array($sessionUserType, [Privilege::ROLE_GOD, Privilege::ROLE_MANAGER], true)
        || ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts);
@endphp

@extends('report.template')

@section('report-title')
    {{ $affiliateTypeLabelPlural }} Reports
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
    @if ($sessionUserType == Privilege::ROLE_GOD || $sessionUserType == Privilege::ROLE_ADMIN)
        <div class="bp-toolbar-actions">
            <a class="bp-button-primary" href="/report/aff-data/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table >
        <thead>
        <tr>
            <th>ID</th>
            <th>{{ $affiliateTypeLabel }}</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>Conversions</th>
            @if($showRevenueColumns)
                <th class="headers">Revenue</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @php
            if ($showRevenueColumns) {
                    $array = [
                        'idrep',
                        'user_name',
                        'Clicks',
                        'UniqueClicks',
                        'Conversions',
                        'Revenue',
                    ];
                } else {
                    $array = [
                        'idrep',
                        'user_name',
                        'Clicks',
                        'UniqueClicks',
                        'Conversions',
                    ];
                }

                $array = array_values(array_filter($array));

                $reporter->between($dates['startDate'], $dates['endDate'],
                new Html(true, $array));
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
