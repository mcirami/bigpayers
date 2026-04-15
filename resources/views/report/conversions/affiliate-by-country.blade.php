@php 
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{$offer->offer_name}} Conversions By Country
@endsection

@section('table-options')

@if(Session::userType() != Privilege::ROLE_AFFILIATE)
    @php
		$data = array(
			'd_from' 		=> $startDate,
			'd_to'			=> $endDate,
			'dateSelect'	=> $dateSelect,
			'user' 			=> $user->idrep,
			'offerId' 		=> $offer->idoffer
		);
	@endphp
	@include('report.options.user-clicks-view', $data)
@endif
    @include('report.options.dates')
    
@endsection

@section('table')
<div class="table_wrap">
    <table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Country</th>
            <th class="value_span9">Clicks</th>
            <th class="value_span9">Unique Clicks</th>
            <th class="value_span9">Conversions</th>
        </tr>
        </thead>
        <tbody>
        @php 
            $params = "d_from=$startDate&d_to=$endDate&dateSelect=$dateSelect";
        @endphp
        @foreach($reports as $key => $row)
            <tr role="row">
                <td>{{$key}}</td>
                <td>{{$row['total_clicks']}}</td>
                <td>{{$row['unique_clicks']}}</td>
                <td>
                    @if ($row['total_conversions'] > 0 && Session::userType() != Privilege::ROLE_AFFILIATE)
                        <a href="/user/{{$user->idrep}}/{{$offer->idoffer}}/subid-conversions-in-country?{{$params}}&country={{$key}}">{{$row['total_conversions']}}</a>
                    @else
                        {{$row['total_conversions']}}    
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection