@extends('report.template')

@section('report-title')
    Offer Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table data-sort-default="6:desc" >
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
        <table>
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
