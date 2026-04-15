@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Conversions By Offer
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection


@section('table')
	{{-- <div class="form-group searchDiv">
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
	</div> --}}
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				<th class="value_span9">Offer Name</th>
				<th class="value_span9">Clicks</th>
				<th class="value_span9">Unique Clicks</th>
				<th class="value_span9">Conversions</th>
			</tr>
			</thead>
			<tbody>
                @php 
                    $params = "d_from=" . $startDate . "&d_to=" . $endDate . "&dateSelect=" . $dateSelect;
                @endphp
			@foreach($report as $row)
				<tr role="row">
					<td>{{$row->offer_name}}</td>
					<td>{{$row->total_clicks}}</td>
                    <td>{{$row->unique_clicks}}</td>
					<td>
                        @if ($row->conversions != 0 && ( (isset( $_GET['role'] ) && $_GET['role'] == 3) || !isset( $_GET['role']) ))
                            <a href='/user/{{$user->idrep}}/{{$row->idoffer}}/conversions-by-subid?{{$params}}'>{{$row->conversions}}
                            </a>
                        @else
                            {{$row->conversions}}
                        @endif
                    </td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	{{ $report->links() }}

@endsection

@section('footer')
    {{-- <script type="text/javascript">
		$(document).ready(function () {

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[2, 1]],
					widgets: ['staticRow']
				})
		});

    </script> --}}
@endsection