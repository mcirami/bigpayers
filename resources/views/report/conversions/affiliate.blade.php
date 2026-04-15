@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks
@endsection

@section('table-options')
	{{-- @php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offerId
		);

	@endphp
	@include('report.options.user-clicks-view', $data) --}}
    @include('report.options.dates')
@endsection

@section('table')
	<div class="form-group searchDiv">
		@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
			<form action="/user/{{$user->idrep}}/search-clicks" method="GET">
				<input id="searchBox"
					   class="form-control"
					   type="text"
					   name="searchValue"
					   placeholder="Search Click ID"
				/>
				<input type="hidden" name="d_from" value="{{$startDate}}">
				<input type="hidden" name="d_to" value="{{$endDate}}">
				<input type="hidden" name="dateSelect" value="{{$dateSelect}}">
				<input type="hidden" name="searchType" value="user">
			</form>
		@endif
	</div>
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
					<th class="value_span9">Click ID</th>
				@endif
				<th class="value_span9">Offer Name</th>
				<th class="value_span9">Conversion Timestamp</th>
				<th class="value_span9">Paid</th>
				<th class="value_span9">Sub 1</th>
				<th class="value_span9">Sub 2</th>
				<th class="value_span9">Sub 3</th>
				<th class="value_span9">Sub 4</th>
				<th class="value_span9">Sub 5</th>
			</tr>
			</thead>
			<tbody>
			@php $myReport = new LeadMax\TrackYourStats\Table\Date;  @endphp
			@foreach($report as $row)
				@php
					$convertionTimeStamp = "";
					if ($row->conversion_timestamp) {
						$convertionTimeStamp = $myReport->convertToEST($row->conversion_timestamp);
					}
					
				@endphp
				<tr role="row">
					@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
						<td>{{$row->idclicks}}</td>
					@endif
					<td>{{$row->offer_name}}</td>
					<td>{{$convertionTimeStamp}}</td>
					<td>{{$row->paid}}</td>
					<td>{{$row->sub1}}</td>
					<td>{{$row->sub2}}</td>
					<td>{{$row->sub3}}</td>
					<td>{{$row->sub4}}</td>
					<td>{{$row->sub5}}</td>
				</tr>


			@endforeach
			</tbody>
		</table>
	</div>
	{{ $reportCollection->links() }}

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[2, 1]],
					widgets: ['staticRow']
				})
		});

    </script>
@endsection