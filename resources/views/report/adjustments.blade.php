@extends('report.template')


@section('report-title')
    Adjusted Sales Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">ID</th>
            <th class="value_span9">Affiliatee</th>
            <th class="value_span9">Click ID</th>
            <th class="value_span9">Offer</th>
            <th class="value_span9">Conv ID</th>
            <th class="value_span9">Paid</th>
            <th class="value_span9">Timestamp (UTC)</th>
            <th class="value_span9">Creator</th>
            <th class="value_span9">Action</th>
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

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection
