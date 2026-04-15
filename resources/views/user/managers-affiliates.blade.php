@extends('layouts.master')

@section('content')
    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer large_table">
            <div class="heading_holder">
                <span class="lft value_span9">View {{$manager->user_name}}'s Affiliates</span>
            </div>
            <div class="clear"></div>
            <div class="white_box manage_aff large_table value_span8">
                <table class="table table-bordered table-striped table_01  ">
                    <thead>
                    <tr>
                        <th class="value_span9">Aff ID</th>
                        <th class="value_span9">First Name</th>
                        <th class="value_span9">Last Name</th>
                        <th class="value_span9">Cell Phone</th>
                        <th class="value_span9">Username</th>
                        <th class="value_span9">Status</th>
                        <th class="value_span9">Referrer User Name</th>
                        <th class="value_span9">Aff Timestamp</th>
                        <th class="value_span9">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($affiliates as $affiliate)
                        <tr>
                            <td>{{$affiliate->idrep}}</td>
                            <td>{{$affiliate->first_name}}</td>
                            <td>{{$affiliate->last_name}}</td>
                            <td>{{$affiliate->cell_phone}}</td>
                            <td>{{$affiliate->user_name}}</td>
                            <td>{{$affiliate->status}}</td>
                            <td>{{$affiliate->referrer->user_name}}</td>
                            <td>{{$affiliate->rep_timestamp}}</td>
                            <td>
                                <a class="btn btn-default btn-sm value_span6-1 value_span4 " href="/aff_update.php?idrep={{$affiliate->idrep}}">Edit</a>
                                <a class="btn btn-default btn-sm" href="#" onclick='adminLogin({{$affiliate->idrep}})'>Login</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @include('report.options.pagination')
            </div>
        </div>


    </div>
    <!--right_panel-->
@endsection