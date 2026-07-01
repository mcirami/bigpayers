@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks
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
	<table id="clicks"  data-sortable-table data-sort-default="2:desc" class="table table-condensed table-bordered table_01">
			<thead>
			<tr>
				@if ($canViewFraudData)
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
			@php $myReport = new \App\Support\LegacyDate;  @endphp
			@foreach($report as $row)
				@php
					$convertionTimeStamp = "";
					if ($row->conversion_timestamp) {
						$convertionTimeStamp = $myReport->convertToEST($row->conversion_timestamp);
					}
					
				@endphp
				<tr role="row">
					@if ($canViewFraudData)
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
	{{ $reportCollection->links() }}

@endsection
