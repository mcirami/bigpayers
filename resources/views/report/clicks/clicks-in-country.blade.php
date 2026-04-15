@php
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
	use Maatwebsite\Excel\Facades\Excel;
	$userType = Session::userType();
@endphp

@extends('report.template')

@section('report-title')
    Clicks in {{$geoCode}}
@endsection

@section('table-options')

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
               class="btn btn-default btn-sm" href="/report/geo/clicks-in-country/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}&country={{$geoCode}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <div class="table_wrap">
        <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
            <thead>
            <tr>
                <th class="value_span9">Click ID</th>
                <th class="value_span9">Timestamp</th>
                <th class="value_span9">Conversion Timestamp</th>
                <th class="value_span9">Paid</th>
                <th class="value_span9">Sub 1</th>
                <th class="value_span9">Sub 2</th>
                <th class="value_span9">Sub 3</th>
                <th class="value_span9">Affiliate</th>
                <th class="value_span9">Offer ID</th>
                <th class="value_span9">Referer</th>
                <th class="value_span9">IP</th>
            </tr>
            </thead>
            <tbody>
            @foreach($report as $key => $row)
                <tr role="row">
                    <td>{{$row->idclicks}}</td>
                    <td>{{$row->first_timestamp}}</td>
                    <td>{{$row->conversion_timestamp}}</td>
                    <td>{{$row->paid}}</td>
                    <td>{{$row->sub1}}</td>
                    <td>{{$row->sub2}}</td>
                    <td>{{$row->sub3}}</td>
                    <td>{{$row->rep_idrep}}</td>
                    <td>{{$row->offer_idoffer}}</td>
                    <td>{{$row->referer}}</td>
                    <td>{{$row->click_geo_ip}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $report->withQueryString()->links() }}
@endsection