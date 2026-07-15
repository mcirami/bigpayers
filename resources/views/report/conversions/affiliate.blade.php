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
	<table id="clicks"  data-sortable-table data-sort-default="2:desc" >
			<thead>
			<tr>
				@if ($canViewFraudData)
					<th>Click ID</th>
				@endif
				<th>Offer Name</th>
				<th>Conversion Timestamp</th>
				<th>Paid</th>
				<th>Sub 1</th>
				<th>Sub 2</th>
				<th>Sub 3</th>
				<th>Sub 4</th>
				<th>Sub 5</th>
			</tr>
			</thead>
			<tbody>
			@php $myReport = new \App\Support\DateHelper;  @endphp
			@foreach($report as $row)
				@php
					$convertionTimeStamp ="";
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
