@php 
    use LeadMax\TrackYourStats\System\Session;
    use App\Privilege;
@endphp

@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s {{ \Illuminate\Support\Str::limit($offer->offer_name, 32) }} Conversions By Country
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
    <table id="mainTable" class="table table-bordered table_01 tablesorter">
        <thead>
        <tr>
            <th class="value_span9">Country</th>
            <th class="value_span9">Raw</th>
            <th class="value_span9">Unique</th>
            <th class="value_span9">Convs</th>
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
                        <a class="bp-report-link" href="/user/{{$user->idrep}}/{{$offer->idoffer}}/subid-conversions-in-country?{{$params}}&country={{$key}}">{{$row['total_conversions']}}</a>
                    @else
                        {{$row['total_conversions']}}    
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('footer')
    <script type="text/javascript">
        $(document).ready(function () {
            $("#mainTable").tablesorter({
                sortList: [[3, 1]],
                widgets: ['staticRow']
            });
        });
    </script>
@endsection
