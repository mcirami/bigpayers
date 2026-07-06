@extends('report.template')


@section('report-title')
    Adjusted Sales Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
    <table  id="mainTable" data-sortable-table >
        <thead>
        <tr>
            <th>ID</th>
            <th>Affiliatee</th>
            <th>Click ID</th>
            <th>Offer</th>
            <th>Conv ID</th>
            <th>Paid</th>
            <th>Timestamp (UTC)</th>
            <th>Creator</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->affiliate_user_name }}</td>
                <td>{{ $row->click_id }}</td>
                <td>{{ $row->offer_name }}</td>
                <td>{{ $row->conversion_id }}</td>
                <td>${{ number_format((float) $row->paid, 2) }}</td>
                <td>{{ $row->timestamp }}</td>
                <td>{{ $row->creator_user_name }}</td>
                <td>CREATE SALE</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        </tfoot>
    </table>
@endsection
