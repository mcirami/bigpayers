@extends('report.template')

@section('report-title')
    Daily Reports
@endsection


@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table data-sort-default="0:desc" >
        <thead>
        <tr>
            <th class="value_span9">Date</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Signups</th>
            <th class="value_span9">Pending</th>
            <th class="value_span9">Convs</th>
            <th class="value_span9">Revenue</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
