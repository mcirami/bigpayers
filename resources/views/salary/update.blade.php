@extends('layouts.master')

@section('content')

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span
                        class="lft">Update salary for {{$user->user_name}}</span>
            </div>


            <form action="/user/{{$user->idrep}}/salary/update" method="post">
                <div class="white_box value_span8">
                    <div class="left_con01">
                        {{csrf_field()}}
                        <p>
                            <label>Salary </label>
                            <input type="number" value="{{$salary->salary}}" name="salary">
                        </p>

                        <p>
                            <label>Status</label>
                            <select name="status">
                                <option {{$salary->status == 1 ? "selected" : ""}} value="1">Active</option>
                                <option {{$salary->status == 0 ? "selected" : ""}} value="0">In-Active</option>
                            </select>
                        </p>


                        <p>

                        </p>

                    </div>

                    <div class="right_con01">
                        <p>
                            <label>Last Update</label>
                            <span>{{\Carbon\Carbon::createFromTimestamp($salary->last_update)->diffForHumans()}}</span>
                        </p>
                        <input class="btn btn-primary" type="submit" value="Save" name="submit">
                    </div>
                </div>
            </form>


        </div>
        <!--right_panel-->
@endsection
