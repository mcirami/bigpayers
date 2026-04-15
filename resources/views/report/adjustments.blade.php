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
            <th class="value_span9">Log ID</th>
            <th class="value_span9">User Name</th>
            <th class="value_span9">Click ID</th>
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Conversion ID</th>
            <th class="value_span9">Paid</th>
            <th class="value_span9">Timestamp (UTC)</th>
            <th class="value_span9">Creator User Name</th>
            <th class="value_span9">Action</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'], new \LeadMax\TrackYourStats\Report\Formats\HTML());
        @endphp
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