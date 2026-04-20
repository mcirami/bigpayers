@extends('report.template')

@section('report-title')
    Advertiser's Conversions By Offer
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
    <table id="mainTable" class="table table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Name</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
            <th class="value_span9">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @foreach($affiliateReport as $row)
            <tr role="row">
                <td>{{$row->offer_name}}</td>
                <td>{{$row->total_clicks}}</td>
                <td>{{$row->unique_clicks}}</td>
                <td>{{$row->conversions}}</td>
                <td>${{$row->total}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4 bp-legacy-pagination">
        {{ $affiliateReport->links() }}
    </div>

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {
			$("#mainTable").tablesorter({
				sortList: [[3, 1]],
				widgets: ['staticRow']
			});
		});
    </script>
@endsection
