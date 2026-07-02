@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'s Conversions By SubId In {{$country}} 
@endsection

@section('table-options')
    @php 
        $params ="d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect&country=$country";
    @endphp
    @include('report.options.dates')
@endsection

@section('table')
    <table id="clicks"  data-sortable-table >
        <thead>
        <tr>
            <th class="value_span9">SubId</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Unique Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reportCollection as $row)
            <tr role="row">
                <td>
                    {{$row->subId}}
                </td>
                <td>
                    <a href="/user/{{$user->idrep}}/{{$offer->idoffer}}/subid-offer-clicks-in-country?{{$params}}&subid={{$row->subId}}">
                        {{$row->total_clicks}}
                    </a>
                </td>    
                <td>{{$row->unique_clicks}}</td>
                <td>
                    {{$row->total_conversions}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
{{ $reportCollection->links() }}
@endsection
