@extends('layouts.master')

@section('content')

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer large_table ">
            <div class="heading_holder">
                <span class="lft value_span9">Email Pools</span>

            </div>


            <h3>Owned Pools</h3>
            <div class="clear"></div>
            <div class="white_box_x_scroll white_box manage_aff  value_span8  ">
                <table class="table table-bordered">
                    <thead>
                    <th>Pool</th>
                    <th>Timestamp</th>
                    <th>Actions</th>
                    </thead>
                    <tbody>
                    @foreach($ownedPools as $pool)
                        <tr>
                            <td>Pool #{{$pool->id}}</td>
                            <td>{{$pool->timestamp}}</td>
                            <td><a href="/email/pools/{{$pool->id}}/download">Download</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>


            </div>

            <div class="clear"></div>
            <h3>Available Pools</h3>
            <div class="white_box_x_scroll white_box manage_aff   value_span8  ">
                <table class="table table-bordered">
                    <thead>
                    <th>Pool</th>
                    <th>Timestamp</th>
                    <th>Actions</th>
                    </thead>
                    <tbody>
                    @foreach($availablePools as $pool)
                        <tr>
                            <td>Pool #{{$pool->id}}</td>
                            <td>{{$pool->timestamp}}</td>
                            <td><a href="/email/pools/{{$pool->id}}/claim">Claim</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@endsection