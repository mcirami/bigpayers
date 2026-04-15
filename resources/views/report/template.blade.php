@extends('layouts.master')
@section('content')
    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer large_table">
            <div class="heading_holder">
                <span class="lft value_span9"> @yield('report-title')</span>
                
            </div>

            <div class='form-group ' style='float:left'>
                @yield('table-options')
            </div>

            <div class="form-group filter_form" style='float:right'>
                @yield('filters')
            </div>

            <div class="clear"></div>
            <div class="white_box manage_aff large_table value_span8 @if(Route::currentRouteName() == "offerClicks" || Route::currentRouteName() == "userClicks" ) adjust_overflow @endif">
                @yield('table')
            </div>

            @yield('extra')

        </div>
    </div>
    <!--right_panel-->


@endsection