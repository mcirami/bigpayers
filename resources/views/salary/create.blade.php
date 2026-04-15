@extends('layouts.master')

@section('content')

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span
                        class="lft">Create salary for {{$user->user_name}}</span>
            </div>


            <form action="/user/{{$user->idrep}}/salary/create" method="post">
                <div class="white_box value_span8">
                    <div class="left_con01">
                        {{csrf_field()}}
                        <p>
                            <label>Salary </label>
                            <input type="number" value="" name="salary">
                        </p>

                        <p>
                            <label>Status</label>
                            <select name="status">
                                <option value="1">Active</option>
                                <option value="0">In-Active</option>
                            </select>
                        </p>


                    </div>

                    <input class="btn btn-primary" type="submit" value="Save" name="submit">
                </div>
        </div>
        </form>


    </div>
    <!--right_panel-->
@endsection
