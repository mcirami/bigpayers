@extends('report.template')

@section('report-title')
    {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'s Conversions By Affiliate
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
	<table  id="mainTable" data-sortable-table data-sort-default="4:desc" >
		<thead>

		<tr>
			<th>ID</th>
			<th>User</th>
			<th>Clicks</th>
			<th>Unique</th>
			<th>Convs</th>
		</tr>
		</thead>
		<tbody>
		@if(!empty($affiliateReport))
			@foreach($affiliateReport as $report)
				<tr>
					<td>{{$report->user_id}}</td>
					<td>{{$report->user_name}}</td>
					<td>{{$report->clicks}}</td>
					<td>{{$report->unique_clicks}}</td>
					<td>{{$report->conversions}}</td>
				</tr>
			@endforeach
		@endif
		</tbody>
	</table>
@endsection
