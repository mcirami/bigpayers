@extends('report.template')

@section('report-title')
    Conversions By Offer in {{$geoCode}}
@endsection

@section('table-options')

    @if(!$isAffiliate)
        @php
            $data = array(
                'd_from' 		=> $startDate,
                'd_to'			=> $endDate,
                'dateSelect'	=> $dateSelect,
            );
        @endphp
    @endif
    @include('report.options.dates')

@endsection

@section('table')
    <table id="mainTable"  data-sortable-table data-sort-default="3:desc" >
        <thead>
        <tr>
            <th>Name</th>
            <th>Raw</th>
            <th>Unique</th>
            <th>Convs</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr role="row">
                <td>{{$row->offer_name}}</td>
                <td>{{$row->total_clicks}}</td>
                <td>{{$row->unique_clicks}}</td>
                <td>{{$row->total_conversions}}</td>
            </tr>
        @endforeach
        <tr class="static" role="row">
            <td>Total</td>
            @foreach($totals as $total)
                <td>{{ $total }}</td>
            @endforeach
        </tr>
        </tbody>
    </table>
@endsection
