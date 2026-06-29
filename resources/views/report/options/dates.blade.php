<script type='text/javascript'>var dateSelect = {{\App\Support\RequestContext::query('dateSelect', 0)}};</script>

<div class="bp-toolbar-cluster">
    <label class="bp-form-field">
        <span class="bp-form-label">Date Range</span>
        <select onchange="handleDateSelect(this);" class="selectBox bp-form-input" id="preDefined" name="preDefined">
            <option {{\App\Support\RequestContext::query('dateSelect') == 0 ? 'selected' : ''}} value='0'>Today</option>
            <option {{\App\Support\RequestContext::query('dateSelect') == 1 ? 'selected' : ''}} value='1'>Yesterday</option>
            <option {{\App\Support\RequestContext::query('dateSelect') == 2 ? 'selected' : ''}} value='2'>Week to Date</option>
            <option {{\App\Support\RequestContext::query('dateSelect') == 5 ? 'selected' : ''}} value='5'>Last Week</option>
            <option {{\App\Support\RequestContext::query('dateSelect') == 7 ? 'selected' : ''}} value='7'>Choose Dates</option>
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
            value='{{\App\Support\RequestContext::query("d_from", \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'
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
            value='{{\App\Support\RequestContext::query('d_to', \Carbon\Carbon::today('America/New_York')->format('Y-m-d'))}}'
        >
    </label>

    <div class="bp-toolbar-actions">
        <button
            id='searchBtn'
            class="bp-button-secondary"
            onclick="window.location = '/{{\App\Support\RequestContext::path() . '?' . http_build_query(\App\Support\RequestContext::queryExcept(['d_from','d_to','dateSelect']))}}' + processDates()">
            Search
        </button>
    </div>
</div>
