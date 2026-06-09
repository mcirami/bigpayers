@extends('report.template')

@section('report-title')
    Sales Log
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
        <table class="table table-bordered table_01 tablesorter" id="mainTable">
            <thead>
            <tr>
                @if($sessionUserType !== \App\Privilege::ROLE_AFFILIATE)
                    <th class="value_span9">Conversion ID</th>
                @endif
                <th class="value_span9">Offer Name</th>
                <th class="value_span9">Pending Timestamp</th>
                <th class="value_span9">Converted Timestamp</th>
                <th class="value_span9">Actions</th>
            </tr>
            </thead>
            <tbody>
            @php
                $reporter->between($dates['startDate'], $dates['endDate'], new \App\Support\LegacyReportHtml());
            @endphp
            </tbody>
        </table>
        @include('report.options.pagination')
@endsection




        @section('footer')
            <script type="text/javascript">
                $(document).ready(function() {
                    $('#mainTable').tablesorter(
                        {
                            sortList: [[2, 1]],
                            widgets: ['staticRow'],
                        });
                });
            </script>
@endsection
