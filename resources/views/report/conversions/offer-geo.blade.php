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
    <table id="mainTable" class="table table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
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

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[3, 1]],
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection
