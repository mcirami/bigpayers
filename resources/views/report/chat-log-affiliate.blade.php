@extends('report.template')

@section('report-title')
    Sales Log
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
        <table  id="mainTable" data-sortable-table data-sort-default="2:desc" >
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
            @foreach($report as $row)
                <tr>
                    @if($sessionUserType !== \App\Privilege::ROLE_AFFILIATE)
                        <td>{{ $row->conversion_id }}</td>
                    @endif
                    <td>{{ $row->offer_name }}</td>
                    <td>{{ $row->timestamp }}</td>
                    <td>{{ $row->conversion_timestamp }}</td>
                    <td>
                        <div class="bp-table-actions">
                            @if($row->sale_log_id !== null)
                                <a href="/chat-log/view/{{ $row->sale_log_id }}" class="bp-action-link">View Log</a>
                            @else
                                <a href="/chat-log/add/{{ $row->pending_conversion_id }}" class="bp-action-link">Log Sale</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @include('report.options.pagination')
@endsection
