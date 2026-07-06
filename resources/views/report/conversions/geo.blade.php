@extends('report.template')

@section('report-title')
    Conversions By Country
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
            <th>Country</th>
            <th>Clicks</th>
            <th>Unique</th>
            <th>Convs</th>
        </tr>
        </thead>
        <tbody>
        @php
            $params ="d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
        @endphp
        @foreach($reports as $key => $row)
            <tr role="row">
                <td>{{$key}}</td>
                <td>
                    @if ($row['total_clicks'] > 0 && ($isGod || $isAdmin))
                        <a class='load_click' href="/report/geo/clicks-in-country?{{$params}}&country={{$key}}">{{$row['total_clicks']}}</a>
                    @else
                        {{$row['total_clicks']}}
                    @endif
                </td>
                <td>
                    {{$row['unique_clicks']}}
                </td>
                <td>
                    @if ($row['total_conversions'] > 0 && ($isGod || $isAdmin))
                        <a href="/report/geo-by-offer?{{$params}}&country={{$key}}">{{$row['total_conversions']}}</a>
                    @else
                        {{$row['total_conversions']}}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
