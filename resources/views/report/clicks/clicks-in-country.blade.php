@php
    use App\Privilege;
	use Maatwebsite\Excel\Facades\Excel;
@endphp

@extends('report.template')

@section('report-title')
    Clicks in {{$geoCode}}
@endsection

@section('table-options')

    @include('report.options.dates')
    @if ($sessionUserType == Privilege::ROLE_GOD || $sessionUserType == Privilege::ROLE_ADMIN)
        <div class="bp-toolbar-actions">
            <a class="bp-button-primary" href="/report/geo/clicks-in-country/export?d_from={{$startDate}}&d_to={{$endDate}}&dateSelect={{$dateSelect}}&country={{$geoCode}}">
                Export Data
            </a>
        </div>
    @endif
@endsection

@section('table')
    <table id="mainTable"  data-sortable-table data-sort-default="1:desc" >
        <thead>
        <tr>
            <th class="value_span9">Click</th>
            <th class="value_span9">Click Time</th>
            <th class="value_span9">Conv Time</th>
            <th class="value_span9">Paid</th>
            <th class="value_span9">Sub 1</th>
            <th class="value_span9">Sub 2</th>
            <th class="value_span9">Sub 3</th>
            <th class="value_span9">Affiliate</th>
            <th class="value_span9">Offer</th>
            <th class="value_span9">IP</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
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
                <td>{{$row->click_geo_ip}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 bp-report-pagination">
        {{ $report->withQueryString()->links() }}
    </div>
@endsection
