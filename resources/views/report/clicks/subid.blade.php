@php
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Sub Id '{{$subId}}' Clicks For Offer '{{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
	@if ($canViewFraudData)
		<div class="bp-report-toolbar searchDiv">
			<form action="/user/{{$user->idrep}}/search-clicks" method="GET">
				<label for="searchBox">Search Click ID</label>
				<input id="searchBox"
					   class="bp-form-input"
					   type="text"
					   name="searchValue"
					   placeholder="Search Click ID"
				/>
				<input type="hidden" name="d_from" value="{{$startDate}}">
				<input type="hidden" name="d_to" value="{{$endDate}}">
				<input type="hidden" name="dateSelect" value="{{$dateSelect}}">
				<input type="hidden" name="searchType" value="user">
			</form>
		</div>
	@endif
	<table id="clicks"  data-sortable-table data-sort-default="4:desc" >
			<thead>
			<tr>
				@if ($canViewFraudData)
					<th>Click ID</th>
				@endif
				<th>Click Time</th>
				<th>Conv Time</th>

                @if ($canViewFraudData || ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts))
                    <th>Paid</th>
                @endif
                @if ($canViewFraudData)
                    <th>Referer Url</th>
                @endif
				@if ($canViewFraudData)
					<th>IP Address</th>
				@endif
				<th>Country</th>
				@if ($canViewFraudData)
					<th>Sub Division</th>
					<th>City</th>
					<th>Postal</th>
					<th>Longitude</th>
					<th>Latitude</th>
				@endif
			</tr>
			</thead>
			<tbody>
			@php $myReport = new \App\Support\LegacyDate;  @endphp
			@foreach($report as $row)
				@php 
					$timestamp = $myReport->convertToEST($row->timestamp);
					$convertionTimeStamp ="";
					if ($row->conversion_timestamp) {
						$convertionTimeStamp = $myReport->convertToEST($row->conversion_timestamp);
					}
					
				@endphp
				<tr role="row">
					@if ($canViewFraudData)
						<td>{{$row->idclicks}}</td>
					@endif
					<td>{{$timestamp}}</td>
					<td>{{$convertionTimeStamp}}</td>
                    @if ($canViewFraudData || ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts))
                        <td>{{$row->paid}}</td>
                    @endif
                    @if ($canViewFraudData)
                        <td>{{$row->referer}}</td>
                    @endif
					@if ($canViewFraudData)
						<td>{{$row->ip_address}}</td>
					@endif
					<td>{{$row->isoCode}}</td>
					@if ($canViewFraudData)
						<td>{{$row->subDivision}}</td>
						<td>{{$row->city}}</td>
						<td>{{$row->postal}}</td>
						<td>{{$row->latitude}}</td>
						<td>{{$row->longitude}}</td>
					@endif

				</tr>


			@endforeach
			</tbody>
	</table>
	{{ $reportCollection->links() }}

@endsection
