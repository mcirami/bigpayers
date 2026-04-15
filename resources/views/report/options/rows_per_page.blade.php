<div class="rows_select">
    <label for="rpp">Rows Per Page:</label>
    <select class="selectBoxTheme" id="rpp" name="rpp"
            onchange="if(this.value !== 'Custom') window.location= '/{{request()->path() . '?' . http_build_query(request()->except(['page','rpp']))}}&rpp='+ this.value + '&page=1'">

        @php($rpp = request()->query('rpp',25))
        <option {{$rpp == 10 ? "selected" :""}} value="10">10</option>
        <option {{$rpp == 15 ? "selected" :""}} value="15">15</option>
        <option {{$rpp == 25 ? "selected": ""}} value="25">25</option>
        <option {{$rpp == 35 ? "selected" :""}} value="35">35</option>
        <option {{$rpp == 45 ? "selected" : ""}} value="45">45</option>
        <option {{$rpp == 50 ? "selected": ""}} value="50">50</option>
        <option {{!in_array($rpp, [10,15,25,35,45,50]) ? "selected" :""}}>Custom</option>
    </select>


    <input class="rows_select selectBoxTheme" type="number" style="width:40px;"
           onchange="window.location ='/{{request()->path() . '?' . http_build_query(request()->except(['path','rpp']))}}&rpp='+this.value + '&page=1'"

           value="{{request()->query('rpp',25)}}" name="rpp" id="rpp"/>
</div>

