@extends('report.template')

@section('report-title')
    Advertiser Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table data-sort-default="5:desc" >
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>Convs</th>
            <th>Revenue</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row['id'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['Clicks'] }}</td>
                <td>{{ $row['UniqueClicks'] }}</td>
                <td>{{ $row['Conversions'] }}</td>
                <td>${{ number_format((float) $row['Revenue'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
