@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{$offer->offer_name}}'s Conversions By SubId In {{$country}} 
@endsection

@section('table-options')
    @php 
        $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect&country=$country";
    @endphp
    {{-- @php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offer->idoffer
		);

	@endphp
	@include('report.options.user-clicks-view', $data) --}}
    @include('report.options.dates')
@endsection

@section('table')
<div class="table_wrap">
    <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">SubId</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Unique Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reportCollection as $row)
            <tr role="row">
                <td>
                    {{$row->subId}}
                </td>
                <td>
                    <a href="/user/{{$user->idrep}}/{{$offer->idoffer}}/subid-offer-clicks-in-country?{{$params}}&subid={{$row->subId}}">
                        {{$row->total_clicks}}
                    </a>
                </td>    
                <td>{{$row->unique_clicks}}</td>
                <td>
                    {{$row->total_conversions}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $reportCollection->links() }}
@endsection