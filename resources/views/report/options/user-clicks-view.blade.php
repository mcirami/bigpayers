@php

    $filterValue = isset($_GET['filter']) ? $_GET['filter'] : "subid";

@endphp
<label class="bp-form-label flex flex-col gap-2">
    <span class="bp-form-label">View</span>
    <select name="filter" id="filter" class="selectBox" onchange="getConversionsView(this);" style="width: 170px;">
        <option value="subid" @php if($filterValue == "subid") { echo "selected"; } @endphp>SubId</option>
        <option value="country" @php if($filterValue == "country") { echo "selected"; } @endphp>Country</option>
    </select>
</label>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        function getConversionsView(element) {
            let data = <?php echo json_encode($data); ?>;
            console.log(data);

            if (element.value == "subid") {
                $slug = "conversions-by-subid";
                $filter = "subid";
            } else {
                $slug = "conversions-by-country";
                $filter = "country";
            }

            window.location.href = '/user/' + 
            data.user + '/' + data.offerId + '/' +
            $slug +
            '?filter=' + $filter + 
            '&d_from=' + data.d_from + 
            '&d_to=' + data.d_to + 
            '&dateSelect=' + data.dateSelect;
        }

        window.getConversionsView = getConversionsView;
    });
</script>