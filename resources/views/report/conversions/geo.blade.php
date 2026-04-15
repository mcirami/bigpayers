@php
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    Conversions By Country
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
                <th class="value_span9">Country</th>
                <th class="value_span9">Clicks</th>
                <th class="value_span9">Unique Clicks</th>
                <th class="value_span9">Conversions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
            @endphp
            @foreach($reports as $key => $row)
                <tr role="row">
                    <td>{{$key}}</td>
                    <td>
                        @if ($row['total_clicks'] > 0 && (Session::userType() == Privilege::ROLE_GOD || Session::userType() == Privilege::ROLE_ADMIN))
                            <a class='load_click' href="/report/geo/clicks-in-country?{{$params}}&country={{$key}}">{{$row['total_clicks']}}</a>
                        @else
                            {{$row['total_clicks']}}
                        @endif
                    </td>
                    <td>
                        {{$row['unique_clicks']}}
                    </td>
                    <td>
                        @if ($row['total_conversions'] > 0 && (Session::userType() == Privilege::ROLE_GOD || Session::userType() == Privilege::ROLE_ADMIN))
                            <a href="/report/geo-by-offer?{{$params}}&country={{$key}}">{{$row['total_conversions']}}</a>
                        @else
                            {{$row['total_conversions']}}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection