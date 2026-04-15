@extends('report.template')

@section('report-title')
    SubID {{$subID}}'s Report
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')

    <table class="table-sm table-bordered table-striped table_01 tablesorter" id="mainTable">
        <thead>
        <tr>
            <th class="value_span9">Offer Name</th>
            <th class="value_span9">Conversion Timestamp</th>
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

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					sortList: [[3, 1]],
					widgets: ['staticRow']
				});
		});
    </script>
@endsection