@extends('report.template')

@section('report-title')
    Click Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Offer ID</th>
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Free Sign Ups</th>
            <th class="value_span9">Pending Conversions</th>
            <th class="value_span9">Conversions</th>
            <th class="value_span9">Revenue</th>
            <th class="value_span9">Deductions</th>
            <th class="value_span9">EPC</th>
            <th class="value_span9">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'], new LeadMax\TrackYourStats\Report\Formats\HTML(true, [
                'idoffer',
                'offer_name',
                'Clicks',
                'UniqueClicks',
                'FreeSignUps',
                'PendingConversions',
                'Conversions',
                'Revenue',
                'Deductions',
                'EPC',
                'TOTAL',
            ]));
        @endphp
        </tbody>
    </table>
    @if($report->bonuses)
        <table class="table table-bordered table_01">
            <thead>
            <tr>
                <td>Bonus Name</td>
                <td>Bonus Revenue</td>
            </tr>
            </thead>
            <tbody>
            @php($report->printBonuses())
            </tbody>
        </table>
    @endif
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
