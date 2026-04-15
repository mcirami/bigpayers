{{--@php--}}
    {{--$aTimeZones = array(--}}
        {{--'America/New_York' => "Eastern",--}}
        {{--'America/Chicago' => 'Central',--}}
        {{--'America/Denver' => 'Mountain',--}}
        {{--'America/Phoenix' => 'Mountain no DST',--}}
        {{--'America/Los_Angeles' => 'Pacific',--}}
        {{--'America/Anchorage' => 'Alaska',--}}
        {{--'America/Adak' => 'Hawaii',--}}
        {{--'Pacific/Honolulu' => 'Hawaii no DST',--}}
    {{--);--}}
{{--@endphp--}}


{{--<b style='margin-left:2px; '>Timezone: </b>--}}
{{--<select class="selectBox" id="timezone" name="timezone"--}}
        {{--onchange='refreshDates();'>--}}
    {{--@foreach($aTimeZones as $zone => $shortHand)--}}
        {{--@if(request()->query('timezone', 'America/Los_Angeles') == $zone)--}}
            {{--<option selected value="{{$zone}}">{{$shortHand}}</option>--}}
        {{--@else--}}
            {{--<option value="{{$zone}}">{{$shortHand}}</option>--}}
        {{--@endif--}}
    {{--@endforeach--}}
{{--</select>--}}


<script type='text/javascript'>var dateSelect = {{request()->query('dateSelect', 0)}};</script>

<script src='/js/tables.js'></script>


<select style='width:170px;' onchange="handleDateSelect(this);" class="selectBox" id="preDefined" name="preDefined">";

    <option {{request()->query('dateSelect') == 0 ? 'selected' : ''}} value='0'>Today</option>
    <option {{request()->query('dateSelect') == 1 ? 'selected' : ''}}  value='1'>Yesterday</option>
    <option {{request()->query('dateSelect') == 2 ? 'selected' : ''}} value='2'>Week to Date</option>
    <option {{request()->query('dateSelect') == 3 ? 'selected' : ''}} value='3'>Month to Date</option>
    <option {{request()->query('dateSelect') == 4 ? 'selected' : ''}} value='4'>Year to Date</option>
    <option {{request()->query('dateSelect') == 5 ? 'selected' : ''}} value='5'>Last Week</option>
    <option {{request()->query('dateSelect') == 6 ? 'selected' : ''}} value='6'>Last Month</option>
    <option {{request()->query('dateSelect') == 7 ? 'selected' : ''}} value='7'>Custom</option>
</select>


<label for='d_from'>From:</label>
<input style='width:100px;' onchange='setCustom();' type="text" id="d_from"
       name="d_from"
       value='{{request()->query("d_from", \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'>

<label for='d_to'>To:</label>
<input style='width:100px;' onchange='setCustom();' type="text" id="d_to" name="d_to"
       value='{{request()->query('d_to', \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'>

<div class="button_wrap">
    <button id='searchBtn' class=" btn btn-default btn-sm"
            onclick="window.location = '/{{request()->path() . "?" . http_build_query(request()->except(['d_from','d_to','dateSelect']))}}' +  processDates()  ">
        Search
    </button>
</div>


