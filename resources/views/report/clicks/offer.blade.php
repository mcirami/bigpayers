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

@section('table')
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
@endsection

@section('extra')
	<div class="bp-card value_span8">
		<div class="bp-legacy-pagination">
			{{ $reportCollection->links() }}
		</div>
	</div>
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
