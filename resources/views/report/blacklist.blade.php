@extends('report.template')

@section('report-title')
    Black List Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table class="table table-bordered table-striped table_01">
        <thead>
        <tr>
            <th class="value_span9">Aff ID</th>
            <th class="value_span9">Affiliate</th>
            <th class="value_span9">Clicks</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reps as $rep)
            <tr @class(['static' => $loop->last])>
                <td>{{ $rep->idrep }}</td>
                <td>{{ $rep->user_name }}</td>
                <td>
                    <a href="/user/{{ $rep->idrep }}/clicks?{{ http_build_query([
                        'd_from' => $startDate,
                        'd_to' => $endDate,
                        'dateSelect' => $dateSelect,
                        'blacklist' => 1,
                    ]) }}">{{ $rep->blacklisted_clicks }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        </tfoot>
    </table>
@endsection
