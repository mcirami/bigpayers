@extends('report.template')

@section('report-title')
    Chat Log Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">User ID</th>
            <th class="value_span9">User Name</th>
            <th class="value_span9">Pending Sales</th>
            <th class="value_span9">Logged Sales</th>
            <th class="value_span9">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row['idrep'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>
                    <a target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
                        'd_from' => $dates['originalStart'],
                        'd_to' => $dates['originalEnd'],
                        'show' => 'nonelogged',
                    ]) }}">{{ $row['pending_sales'] }}</a>
                </td>
                <td>
                    <a target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
                        'd_from' => $dates['originalStart'],
                        'd_to' => $dates['originalEnd'],
                        'show' => 'logged',
                    ]) }}">{{ $row['logged_sales'] }}</a>
                </td>
                <td>
                    <a target="_blank" href="/report/chat-log/{{ $row['idrep'] }}?{{ http_build_query([
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



@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#mainTable').tablesorter(
                {
                    sortList: [[6, 1]],
                    widgets: ['staticRow'],
                });
        });
    </script>
@endsection
