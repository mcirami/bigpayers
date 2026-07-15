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
	<table id="clicks"  data-sortable-table data-sort-default="3:desc" >
				<thead>
				<tr>
					@if ($canViewFraudData)
						<th>Click ID</th>
					@endif
					@if ($canViewFraudData)
						<th>Encoded ID</th>
					@endif
					<th><br>Click Time</th>
					<th>Conv Time</th>
                    @if($sessionUserType == Privilege::ROLE_GOD ||
                        ($sessionUserType == Privilege::ROLE_ADMIN && $canViewPayouts)
                    )
                        <th>Paid</th>
                    @endif
					<th>Sub 1</th>
					<th>Sub 2</th>
					<th>Sub 3</th>
					<th>Affiliate</th>
					<th>Offer</th>
                    @if ($canViewFraudData)
                        <th>Referer Url</th>
                    @endif
					@if ($canViewFraudData)
						<th>Ip Address</th>
						<th>Sub Division</th>
						<th>City</th>
						<th>Postal</th>
						<th>Longitude</th>
						<th>Latitude</th>
					@endif
					<th>Country</th>
				</tr>
				</thead>
				<tbody>
				@php $myReport = new \App\Support\DateHelper;  @endphp
				@foreach($report as $row)
				
					@php 
						$timestamp = $myReport->convertToEST($row['timestamp']);
						$convertionTimeStamp ="";
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
                                <td>{{isset($row['ip_address']) ? $row['ip_address'] :""}}</td>
                                <td>{{isset($row['subDivision']) ? $row['subDivision'] :""}}</td>
                                <td>{{isset($row['city']) ? $row['city'] :""}}</td>
                                <td>{{isset($row['postal']) ? $row['postal'] :""}}</td>
                                <td>{{isset($row['latitude']) ? $row['postal'] :""}}</td>
                                <td>{{isset($row['longitude']) ? $row['longitude'] :""}}</td>
						@endif
                            <td>{{isset($row['isoCode']) ? $row['isoCode'] :""}}</td>
					</tr>
				@endforeach
				<tr>
				</tr>
				</tbody>
	</table>
@endsection

@section('extra')
	<div class="bp-card">
		<div class="bp-report-pagination">
			{{ $reportCollection->links() }}
		</div>
	</div>
@endsection
