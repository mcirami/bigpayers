@extends('report.template')

@section('report-title')
    Sub Reports
@endsection

@section('table-options')
    <label class="bp-form-label flex flex-col gap-2">
        <span class="bp-form-label">Sub</span>
        <select class='selectBox' id='sub' name='sub'
                onchange="window.location = '/{{\App\Support\RequestContext::path() . '?' . http_build_query(\App\Support\RequestContext::queryExcept(['sub','d_from', 'd_to','timezone','dateSelect']))}}&sub=' + getSubVal() + processDates()">
            @for($i = 1; $i <= 3; $i++)
                @if(\App\Support\RequestContext::query('sub') == $i)
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
    <table  id="mainTable" data-sortable-table data-sort-default="4:desc" >
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
            $linkParams = [];

            foreach (['d_from', 'd_to', 'dateSelect', 'role'] as $queryKey) {
                $queryValue = \App\Support\RequestContext::query($queryKey);

                if ($queryValue !== null) {
                    $linkParams[$queryKey] = $queryValue;
                }
            }
        @endphp
        @foreach($report as $row)
            <tr @class(['static' => $row['sub'] === 'TOTAL'])>
                <td>{{ $row['sub'] }}</td>
                <td>{{ $row['clicks'] }}</td>
                <td>{{ $row['unique'] }}</td>
                <td>
                    @if($row['conversions'] > 0 && ! in_array($row['sub'], ['TOTAL', '(empty)'], true))
                        <a class="bp-report-link" href="{{ '/report/sub/conversions?' . http_build_query(array_merge(['subid' => $row['sub']], $linkParams)) }}">
                            {{ $row['conversions'] }}
                        </a>
                    @else
                        {{ $row['conversions'] }}
                    @endif
                </td>
                <td>{{ is_numeric($row['revenue']) ? number_format($row['revenue'], 2) : $row['revenue'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
