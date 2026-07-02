@extends('report.template')

@section('report-title')
    SubID {{$subID}}'s Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')

    <table  id="mainTable" data-sortable-table data-sort-default="3:desc" >
        <thead>
        <tr>
            <th class="value_span9">Offer</th>
            <th class="value_span9">Conv Time</th>
            <th class="value_span9">Paid</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subReport as $row)
            <tr>
                <td>
                    @php echo $row->offer_name @endphp
                </td>
                <td>
                    @php echo $row->timestamp @endphp
                </td>
                <td>
                    @php echo $row->paid @endphp
                </td>
            </tr>

        @endforeach
        </tbody>
    </table>
@endsection
