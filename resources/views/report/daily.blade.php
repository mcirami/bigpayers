@extends('report.template')

@section('report-title')
    Daily Reports
@endsection


@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Date</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Deductions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{$row['aggregate_date']}}</td>
                <td>{{$row['clicks']}}</td>
                <td>{{$row['unique_clicks']}}</td>
                <td>{{$row['free_sign_ups']}}</td>
                <td>{{$row['pending_conversions']}}</td>
                <td>{{$row['conversions']}}</td>
                <td>{{$row['revenue']}}</td>
                <td>{{$row['deductions']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

