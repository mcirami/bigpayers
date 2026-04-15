@php
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    Conversions By Offer in {{$geoCode}}
@endsection

@section('table-options')

    @if(Session::userType() != Privilege::ROLE_AFFILIATE)
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
    <div class="table_wrap">
        <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
            <thead>
            <tr>
                <th class="value_span9">Offer</th>
                <th class="value_span9">Clicks</th>
                <th class="value_span9">Unique Clicks</th>
                <th class="value_span9">Conversions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
            @endphp
            @foreach($report as $key => $row)
                <tr role="row">
                    <td>{{$row->offer_name}}</td>
                    <td>{{$row->total_clicks}}</td>
                    <td>{{$row->unique_clicks}}</td>
                    <td>
                        {{$row->total_conversions}}
                    </td>
                </tr>
            @endforeach
            <tr role="row">
                <td></td>
                @foreach($totals as $total)
                    <td>
                        {{ $total }}
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
    </div>

@endsection