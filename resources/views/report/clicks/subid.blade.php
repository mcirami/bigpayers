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
	<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				@if ($canViewFraudData)
					<th class="value_span9">Click ID</th>
				@endif
				<th class="value_span9">Click Time</th>
				<th class="value_span9">Conv Time</th>

                @if ($canViewFraudData || ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts))
                    <th class="value_span9">Paid</th>
                @endif
                @if ($canViewFraudData)
                    <th class="value_span9">Referer Url</th>
                @endif
				@if ($canViewFraudData)
					<th class="value_span9">IP Address</th>
				@endif
				<th class="value_span9">Country</th>
				@if ($canViewFraudData)
					<th class="value_span9">Sub Division</th>
					<th class="value_span9">City</th>
					<th class="value_span9">Postal</th>
					<th class="value_span9">Longitude</th>
					<th class="value_span9">Latitude</th>
				@endif
			</tr>
			</thead>
			<tbody>
			@php $myReport = new \App\Support\LegacyDate;  @endphp
			@foreach($report as $row)
				@php 
					$timestamp = $myReport->convertToEST($row->timestamp);
					$convertionTimeStamp = "";
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

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[4, 1]],
					widgets: ['staticRow']
				})

				// bind to pager events
				// *********************
				/*.bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c) {
					var msg = '"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
						' page <span class="typ">' + (c.page + 1) + '/' + c.totalPages + '</span>';
					$('#display')
					.append('<li><span class="str">"' + e.type + msg + '</li>')
					.find('li:first').remove();
				})*/

				// initialize the pager plugin
				// ****************************
				//.tablesorterPager(pagerOptions);
		});

    </script>
@endsection
