@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{$subId}}'s' {{$offer->offer_name}}'s' Conversions By Country
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
<div class="table_wrap">
    <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Country</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Unique Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @php 
            $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect&subid=$subId";
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
</div>

@endsection