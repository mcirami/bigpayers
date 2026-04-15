<div class='controls full_width'>
    @if($paginate->page_total() > 1)




        @if ($paginate->has_previous())
            <a class='first' href='/{{request()->path() . '?' . http_build_query(request()->except('page'))}}&page=1'>
                <img src='/images/icon-first-page.png' alt='First'> </a>
            <a class='previous'
               href='/{{request()->path() . '?' . http_build_query(request()->except('page'))}}&page={{$paginate->previous()}}'><img
                        src='/images/icon-previous-arrow.png' alt='Previous'> </a>
        @endif

        <div class="pages">
            <label for="page">Page &nbsp;</label>
            <select onchange="window.location = '/{{request()->path() . '?' . http_build_query(request()->except('page'))}}&page='+getElementById('page').value ;"
                    id="page" name="page">

                @for($int = 1; $int <= $paginate->page_total(); $int++)
                    @if($int == $paginate->current_page)
                        <option selected value="{{$int}}">{{$int}}</option>
                    @else
                        <option value="{{$int}}">{{$int}}</option>
                    @endif
                @endfor
            </select> of <span class="total">{{$paginate->page_total()}}</span>
        </div>


        @if ($paginate->has_next())
            <a class='next'
               href='/{{request()->path() . '?' . http_build_query(request()->except('page'))}}&page={{$paginate->next()}}'>
                <img src='/images/icon-next-arrow.png' alt='Next'> </a>

            <a class='last'
               href='/{{request()->path() . '?' . http_build_query(request()->except('page'))}}&page={{$paginate->page_total()}}'>
                <img src='/images/icon-last-page.png' alt='Last'>
            </a>
        @endif
    @endif


@include('report.options.rows_per_page')
