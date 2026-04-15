@php

    $filterValue = isset($_GET['filter']) ? $_GET['filter'] : "affiliate";

@endphp

<form>
    <label for="filter">Filter By:</label>
    <select name="filter" id="filter" class="selectBox" onchange="handleFilterSelect(this);">
        <option value="affiliate" @php if($filterValue == "affiliate") { echo "selected"; } @endphp>Affiliate</option>
        <option value="manager" @php if($filterValue == "manager") { echo "selected"; } @endphp>Manager</option>
    </select>
</form>
