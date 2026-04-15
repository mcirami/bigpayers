@php

    $filterValue = isset($_GET['filter']) ? $_GET['filter'] : "affiliate";

@endphp

<select name="filter" id="filter" class="selectBox" onchange="getConversionsView(this);" style="width: 170px; margin-bottom: 20px;">
    <option value="affiliate" @php if($filterValue == "affiliate") { echo "selected"; } @endphp>Affiliate View</option>
    <option value="country" @php if($filterValue == "country") { echo "selected"; } @endphp>Country View</option>
</select>

<style>
    #loading_spinner {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
    }

    #loading_spinner svg {
        width: 250px;
        height: 250px;
        color: #fff;
    }

</style>
<div id="loading_spinner">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="3" r="0">
            <animate id="spinner_318l" begin="0;spinner_cvkU.end-0.5s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
        <circle cx="16.50" cy="4.21" r="0">
            <animate id="spinner_g5Gj" begin="spinner_318l.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
        <circle cx="7.50" cy="4.21" r="0"><animate id="spinner_cvkU" begin="spinner_Uuk0.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="19.79" cy="7.50" r="0"><animate id="spinner_e8rM" begin="spinner_g5Gj.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="4.21" cy="7.50" r="0"><animate id="spinner_Uuk0" begin="spinner_z7ol.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="21.00" cy="12.00" r="0">
            <animate id="spinner_MooL" begin="spinner_e8rM.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="3.00" cy="12.00" r="0"><animate id="spinner_z7ol" begin="spinner_KEoo.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="19.79" cy="16.50" r="0"><animate id="spinner_btyV" begin="spinner_MooL.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle><circle cx="4.21" cy="16.50" r="0">
            <animate id="spinner_KEoo" begin="spinner_1IYD.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="16.50" cy="19.79" r="0"><animate id="spinner_1sIS" begin="spinner_btyV.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="7.50" cy="19.79" r="0"><animate id="spinner_1IYD" begin="spinner_NWhh.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/></circle>
        <circle cx="12" cy="21" r="0">
            <animate id="spinner_NWhh" begin="spinner_1sIS.begin+0.1s" attributeName="r" calcMode="spline" dur="0.6s" values="0;2;0" keyTimes="0;.2;1" keySplines="0,1,0,1;.53,0,.61,.73" fill="freeze"/>
        </circle>
    </svg>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        function getConversionsView(element) {
            let data = <?php echo json_encode($data); ?>;
	        const searchBtn = document.getElementById('searchBtn');
	        searchBtn.disabled = true;
	        const spinner = document.getElementById('loading_spinner');
	        spinner.style.display = 'flex';

            if (element.value === "affiliate") {
                $slug = "user-conversions";
                $filter = "affiliate";
            } else {
                $slug = "conversions-by-country";
                $filter = "country";
            }

            window.location.href = '/report/offer/' + 
            data.offerId + '/' +
            $slug +
            '?filter=' + $filter + 
            '&d_from=' + data.d_from + 
            '&d_to=' + data.d_to + 
            '&dateSelect=' + data.dateSelect;
        }

        window.getConversionsView = getConversionsView;
    });
</script>