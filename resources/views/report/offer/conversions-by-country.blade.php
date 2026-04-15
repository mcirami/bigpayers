@extends('report.template')

@section('report-title')
    {{$offer->offer_name}}'s Conversions By Country
@endsection

@section('table-options')
	@php
		$data = array(
			'd_from' 		=> request()->query('d_from'),
			'd_to'			=> request()->query('d_to'),
			'dateSelect'	=> request()->query('dateSelect'),
			'offerId' 		=> $offer->idoffer
		);
	@endphp
	@include('report.options.offer_conversions_view', $data)
    @include('report.options.dates')
@endsection

@section('table')
	<table class="table table-bordered table_01 tablesorter" id="mainTable">
		<thead>

		<tr>
			<th class="value_span9">Country</th>
			<th class="value_span9">Clicks</th>
			<th class="value_span9">Unique Clicks</th>
			<th class="value_span9">Conversions</th>
		</tr>
		</thead>
		<tbody>
		@if(!empty($affiliateReport))
			@foreach($affiliateReport as $report)
				<tr>
					<td>{{$report['country_code']}}</td>
					<td>{{$report['total_clicks']}}</td>
					<td>{{$report['unique_clicks']}}</td>
					<td>{{$report['total_conversions']}}</td>
				</tr>

			@endforeach
		@endif
		</tbody>
	</table>
@endsection


@section('footer')
    <script type="text/javascript">

		$(document).ready(function () {
			$('#mainTable').tablesorter(
				{
					sortList: [[3, 1]],
					widgets: ['staticRow'],
				});
		});
    </script>
@endsection