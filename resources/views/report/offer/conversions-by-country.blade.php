@extends('report.template')

@section('report-title')
    {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'s Conversions By Country
@endsection

@section('table-options')
	@php
		$data = array(
			'd_from' 		=> \App\Support\RequestContext::query('d_from'),
			'd_to'			=> \App\Support\RequestContext::query('d_to'),
			'dateSelect'	=> \App\Support\RequestContext::query('dateSelect'),
			'offerId' 		=> $offer->idoffer
		);
	@endphp
	@include('report.options.offer_conversions_view', $data)
    @include('report.options.dates')
@endsection

@section('table')
	<table  id="mainTable" data-sortable-table data-sort-default="3:desc" >
		<thead>

		<tr>
			<th class="value_span9">Country</th>
			<th class="value_span9">Clicks</th>
			<th class="value_span9">Unique</th>
			<th class="value_span9">Convs</th>
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
