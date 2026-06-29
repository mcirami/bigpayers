@php
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }}'s Clicks
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

{{--@section('filters')
	@include('report.options.filters')
@endsection--}}

@section('table')
	{{--<div class="table_wrap">
		<table id="reps" class="table table-striped table-bordered table-condensed table_01 tablesorter offer_clicks">
			<thead>
			<tr>
				<th class="value_span9">Affiliate ID</th>
				<th class="value_span9">Username</th>
				<th class="value_span9">Clicks</th>
				<th class="value_span9">Conversions</th>
			</tr>
			</thead>
			<tbody>

			@foreach($affiliateReport as $row)
				<tr>
					<td>{{$row->user_id}}</td>
					<td>{{$row->user_name}}</td>
					<td>{{$row->clicks}}</td>
					<td>{{$row->conversions}}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	<div id="pager" class="pager">
		<form>
			<div class="navigation">
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-bar-left first" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M11.854 3.646a.5.5 0 0 1 0 .708L8.207 8l3.647 3.646a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708 0zM4.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-left prev" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
				</svg>
				<!-- the "pagedisplay" can be any element, including an input -->
				<span class="pagedisplay" data-pager-output-filtered="{startRow:input} &ndash; {endRow} / {filteredRows} of {totalRows} total rows"></span>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-right next" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-bar-right last" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M4.146 3.646a.5.5 0 0 0 0 .708L7.793 8l-3.647 3.646a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708 0zM11.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
				</svg>
				<select class="pagesize">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="all">All Rows</option>
				</select>
			</div>

		</form>
	</div>--}}
	@if ($canViewFraudData)
		<div class="bp-report-toolbar searchDiv">
			<form action="/offer/{{$offer->idoffer}}/search-clicks" method="GET">
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
				<input type="hidden" name="searchType" value="offer">

			</form>
		</div>
	@endif
    <div class="white_box_x_scroll white_box manage_aff large_table value_span8  one_hungee_table adjust_overflow"
         style="width:100%;!important;">
		<div class="table_wrap">
			<table id="clicks" class="table table-striped table-bordered table_01 tablesorter">
				<thead>
				<tr>
					@if ($canViewFraudData)
						<th class="value_span9">Click ID</th>
					@endif
					@if ($canViewFraudData)
						<th class="value_span9">Encoded ID</th>
					@endif
					<th class="value_span9"><br>Click Time</th>
					<th class="value_span9">Conv Time</th>
                    @if($sessionUserType == Privilege::ROLE_GOD ||
                        ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts)
                    )
                        <th class="value_span9">Paid</th>
                    @endif
					<th class="value_span9">Sub 1</th>
					<th class="value_span9">Sub 2</th>
					<th class="value_span9">Sub 3</th>
					{{-- <th class="value_span9">Sub 4</th>
					<th class="value_span9">Sub 5</th> --}}
					<th class="value_span9">Affiliate</th>
					<th class="value_span9">Offer</th>
                    @if ($canViewFraudData)
                        <th class="value_span9">Referer Url</th>
                    @endif
					@if ($canViewFraudData)
						<th class="value_span9">Ip Address</th>
						<th class="value_span9">Sub Division</th>
						<th class="value_span9">City</th>
						<th class="value_span9">Postal</th>
						<th class="value_span9">Longitude</th>
						<th class="value_span9">Latitude</th>
					@endif
					<th class="value_span9">Country</th>
				</tr>
				</thead>
				<tbody>
				@php $myReport = new \App\Support\LegacyDate;  @endphp
				@foreach($report as $row)
				
					@php 
						$timestamp = $myReport->convertToEST($row['timestamp']);
						$convertionTimeStamp = "";
						if ($row->conversion_timestamp) {
							$convertionTimeStamp = $myReport->convertToEST($row['conversion_timestamp']);
						}
					@endphp
					<tr>
						@if ($canViewFraudData)
							<td>{{$row['id']}}</td>
						@endif
                        @if ($canViewFraudData)
                            <td>{{$row['encoded']}}</td>
                        @endif
						<td>{{$timestamp}}</td>
						<td>{{$convertionTimeStamp}}</td>
                        @if ($canViewFraudData ||
                            ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts))
                            <td>{{$row['paid']}}</td>
                        @endif
						@for($i = 1; $i <= 3; $i++)
							<td>{{$row['sub' . $i]}}</td>
						@endfor
						<td>{{$row['affiliate_id']}}</td>
						<td>{{$row['offer_id']}}</td>
						@if ($canViewFraudData)
                                <td>{{$row['referer']}}</td>
                                <td>{{isset($row['ip_address']) ? $row['ip_address'] : ""}}</td>
                                <td>{{isset($row['subDivision']) ? $row['subDivision'] : ""}}</td>
                                <td>{{isset($row['city']) ? $row['city'] : ""}}</td>
                                <td>{{isset($row['postal']) ? $row['postal'] : ""}}</td>
                                <td>{{isset($row['latitude']) ? $row['postal'] : ""}}</td>
                                <td>{{isset($row['longitude']) ? $row['longitude'] : ""}}</td>
						@endif
                            <td>{{isset($row['isoCode']) ? $row['isoCode'] : ""}}</td>
					</tr>
				@endforeach
				<tr>
				</tr>
				</tbody>
			</table>
		</div>
@endsection

@section('extra')
			{{ $reportCollection->links() }}
@endsection

@section('footer')
    <script type="text/javascript">

        $(document).ready(function () {
			$("#clicks")
					// Initialize tablesorter
					// ***********************
					.tablesorter({
						sortList: [[3, 1]],
						widgets: ['staticRow']
					})

					// bind to pager events
					// *********************
					.bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c) {
						var msg = '"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
								' page <span class="typ">' + (c.page + 1) + '/' + c.totalPages + '</span>';
						$('#display')
						.append('<li><span class="str">"' + e.type + msg + '</li>')
						.find('li:first').remove();
					})
        });

    </script>
@endsection
