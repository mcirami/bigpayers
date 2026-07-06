@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{ $offerData->offer_name }} Conversions By Sub Id's
@endsection

@section('table-options')
	@php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offerData->idoffer
		);

	@endphp
	@include('report.options.user-clicks-view', $data)
    @include('report.options.dates')
@endsection

@section('table')
	<table id="clicks"  data-sortable-table data-sort-default="3:desc" >
			<thead>
			<tr>
				<th>Sub Id</th>
				<th>Clicks</th>
				<th>Unique Clicks</th>
				<th>Conversions</th>
			</tr>
			</thead>
			<tbody>
				@php 
					$params ="d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
				@endphp
			@foreach($report as $row)
				<tr role="row">
					<td>{{$row->sub1}}</td>
					<td>
						<a href="/user/{{$user->idrep}}/{{$offerData->idoffer}}/subid-clicks-by-offer?{{$params}}&subId={{$row->sub1}}">
							{{$row->clicks}}
						</a>

					</td>
					<td>
						{{$row->unique_clicks}}
					</td>
					<td>
						@if($row->conversions > 0)
							<a href="/user/{{$user->idrep}}/{{$offerData->idoffer}}/subid-offer-conversions-by-country?{{$params}}&subid={{$row->sub1}}">
								{{$row->conversions}}
							</a>
						@else
							{{$row->conversions}}
						@endif
					</td>
				</tr>
			@endforeach
			</tbody>
	</table>
	{{ $report->links() }}

@endsection
