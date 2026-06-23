@php

    $filterValue = \App\Support\RequestContext::query('filter', "affiliate");

@endphp

<form>
    <label for="filter">Filter By:</label>
    <select name="filter" id="filter" class="selectBox" onchange="handleFilterSelect(this);">
        <option value="affiliate" @php if($filterValue == "affiliate") { echo "selected"; } @endphp>{{ $affiliateTypeLabel }}</option>
        <option value="manager" @php if($filterValue == "manager") { echo "selected"; } @endphp>{{ $accountTypeLabel }}</option>
    </select>
</form>
