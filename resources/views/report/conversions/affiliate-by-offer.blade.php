@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Conversions By Offer
@endsection

@section('table-options')
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
        @php
            $params = "d_from=" . $startDate . "&d_to=" . $endDate . "&dateSelect=" . $dateSelect;
        @endphp
        @foreach($report as $row)
            <tr role="row">
                <td>{{$row->offer_name}}</td>
                <td>{{$row->total_clicks}}</td>
                <td>{{$row->unique_clicks}}</td>
                <td>
                    @if ($row->conversions != 0 && ((isset($_GET['role']) && $_GET['role'] == 3) || !isset($_GET['role'])))
                        <a class="bp-report-link" href='/user/{{$user->idrep}}/{{$row->idoffer}}/conversions-by-subid?{{$params}}'>{{$row->conversions}}</a>
                    @else
                        {{$row->conversions}}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 bp-legacy-pagination">
        {{ $report->links() }}
    </div>

@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter({
                sortList: [[3, 1]],
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
