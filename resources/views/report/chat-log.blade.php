@extends('report.template')

@section('report-title')
    Chat Log Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">User ID</th>
            <th class="value_span9">User Name</th>
            <th class="value_span9">Pending Sales</th>
            <th class="value_span9">Logged Sales</th>
            <th class="value_span9">Total</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'], new \LeadMax\TrackYourStats\Report\Formats\HTML());
        @endphp
        </tbody>
    </table>
@endsection



@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#mainTable').tablesorter(
                {
                    sortList: [[6, 1]],
                    widgets: ['staticRow'],
                });
        });
    </script>
@endsection