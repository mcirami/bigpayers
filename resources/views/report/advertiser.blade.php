@extends('report.template')

@section('report-title')
    Advertiser Reports
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
    <table class="table table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">ID</th>
            <th class="value_span9">Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
            <th class="value_span9">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @foreach($report as $row)
            <tr>
                <td>{{ $row['id'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['Clicks'] }}</td>
                <td>{{ $row['UniqueClicks'] }}</td>
                <td>{{ $row['Conversions'] }}</td>
                <td>${{ number_format((float) $row['Revenue'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter(
                {
                    sortList: [[5, 1]],
                    widgets: ['staticRow']
                });
        });
    </script>
@endsection
