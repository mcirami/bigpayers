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

<div class="bp-toolbar-cluster">
    <label class="bp-form-field">
        <span class="bp-form-label">Date Range</span>
        <select onchange="handleDateSelect(this);" class="selectBox bp-form-input" id="preDefined" name="preDefined">
            <option {{request()->query('dateSelect') == 0 ? 'selected' : ''}} value='0'>Today</option>
            <option {{request()->query('dateSelect') == 1 ? 'selected' : ''}} value='1'>Yesterday</option>
            <option {{request()->query('dateSelect') == 2 ? 'selected' : ''}} value='2'>Week to Date</option>
            <option {{request()->query('dateSelect') == 5 ? 'selected' : ''}} value='5'>Last Week</option>
            <option {{request()->query('dateSelect') == 7 ? 'selected' : ''}} value='7'>Choose Dates</option>
        </select>
    </label>

    <label class="bp-form-field">
        <span class="bp-form-label">From</span>
        <input
            class="bp-form-input"
            onchange='setCustom();'
            type="text"
            id="d_from"
            name="d_from"
            value='{{request()->query("d_from", \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'
        >
    </label>

    <label class="bp-form-field">
        <span class="bp-form-label">To</span>
        <input
            class="bp-form-input"
            onchange='setCustom();'
            type="text"
            id="d_to"
            name="d_to"
            value='{{request()->query('d_to', \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'
        >
    </label>

    <div class="button_wrap bp-toolbar-actions">
        <button
            id='searchBtn'
            class="bp-button-secondary"
            onclick="window.location = '/{{request()->path() . '?' . http_build_query(request()->except(['d_from','d_to','dateSelect']))}}' + processDates()">
            Search
        </button>
    </div>
</div>

