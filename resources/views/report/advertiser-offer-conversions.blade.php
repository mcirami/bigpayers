@extends('report.template')

@section('report-title')
    Advertiser's Conversions By Offer
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
    <div class="table_wrap">
        <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
            <thead>
            <tr>
                <th class="value_span9">Offer Name</th>
                <th class="value_span9">Clicks</th>
                <th class="value_span9">Unique Clicks</th>
                <th class="value_span9">Conversions</th>
                <th class="value_span9">Total</th>
            </tr>
            </thead>
            <tbody>
            @php
                $params = "d_from=" . $startDate . "&d_to=" . $endDate . "&dateSelect=" . $dateSelect;
            @endphp
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
    </div>
    {{ $affiliateReport->links() }}

@endsection

@section('footer')
    {{-- <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[2, 1]],
					widgets: ['staticRow']
				})
		});

    </script> --}}
@endsection