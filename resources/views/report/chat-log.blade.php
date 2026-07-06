@extends('report.template')

@section('report-title')
    Chat Log Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table  id="mainTable" data-sortable-table data-sort-default="6:desc" >
        <thead>
        <tr>
            <th>User ID</th>
            <th>User Name</th>
            <th>Pending Sales</th>
            <th>Logged Sales</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row['idrep'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>
                    <a class="bp-report-link" target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
                        'd_from' => $dates['originalStart'],
                        'd_to' => $dates['originalEnd'],
                        'show' => 'nonelogged',
                    ]) }}">{{ $row['pending_sales'] }}</a>
                </td>
                <td>
                    <a class="bp-report-link" target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
                        'd_from' => $dates['originalStart'],
                        'd_to' => $dates['originalEnd'],
                        'show' => 'logged',
                    ]) }}">{{ $row['logged_sales'] }}</a>
                </td>
                <td>
                    <a class="bp-report-link" target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
                        'd_from' => $dates['originalStart'],
                        'd_to' => $dates['originalEnd'],
                        'show' => 'all',
                    ]) }}">{{ $row['total'] }}</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
