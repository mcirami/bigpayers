@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{$subId}}'s' {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'s' Conversions By Country
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table id="clicks"  data-sortable-table >
        <thead>
        <tr>
            <th>Country</th>
            <th>Clicks</th>
            <th>Unique Clicks</th>
            <th>Conversions</th>
        </tr>
        </thead>
        <tbody>
        @php 
            $params ="d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect&subid=$subId";
        @endphp
        @foreach($reports as $key => $row)
            <tr role="row">
                <td>{{$row['country_code']}}</td>
                <td>
                    <a href="/user/{{$user->idrep}}/{{$offer->idoffer}}/subid-offer-clicks-in-country?{{$params}}&country={{$row['country_code']}}">
                        {{$row['total_clicks']}}
                   </a>
                </td>
                <td>{{$row['unique_clicks']}}</td>
                <td>
                    {{$row['total_conversions']}}   
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
