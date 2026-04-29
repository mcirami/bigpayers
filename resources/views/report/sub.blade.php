@extends('report.template')

@section('report-title')
    Sub Reports
@endsection

@section('table-options')
    <label class="bp-form-label flex flex-col gap-2">
        <span class="bp-form-label">Sub</span>
        <select class='selectBox' id='sub' name='sub'
                onchange="window.location = '/{{request()->path() . '?' . http_build_query(request()->except(['sub','d_from', 'd_to','timezone','dateSelect']))}}&sub=' + getSubVal() + processDates() ">
            @for($i = 1; $i <= 3; $i++)
                @if(request()->query('sub') == $i)
                    <option selected value="{{$i}}">Sub {{$i}}</option>
                @else
                    <option value="{{$i}}">Sub {{$i}}</option>
                @endif
            @endfor
        </select>
    </label>
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Sub</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Conv</th>
            <th class="value_span9">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @php
            $reporter->between($dates['startDate'], $dates['endDate'], new \LeadMax\TrackYourStats\Report\Formats\HTML(true));
        @endphp
        </tbody>
    </table>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[4, 1]],
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection