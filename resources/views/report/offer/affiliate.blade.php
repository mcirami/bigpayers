@extends('report.template')

@section('report-title')
    Offer Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">ID</th>
            <th class="value_span9">Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
            <th class="value_span9">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'], new \App\Support\LegacyReportHtml(true, [
                'idoffer',
                'offer_name',
                'Clicks',
                'UniqueClicks',
                'Conversions',
                'Revenue',
            ]));
        @endphp
        </tbody>
    </table>
    @if($bonusRows->isNotEmpty())
        <table class="table table-bordered table_01">
            <thead>
            <tr>
                <td>Bonus Name</td>
                <td>Bonus Revenue</td>
            </tr>
            </thead>
            <tbody>
            @foreach($bonusRows as $bonus)
                <tr>
                    <td>{{ $bonus->name }}</td>
                    <td>${{ number_format((float) $bonus->payout, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                <td>${{ number_format((float) $bonusRows->sum('payout'), 2) }}</td>
            </tr>
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
