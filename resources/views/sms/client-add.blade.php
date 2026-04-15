@extends('layouts.master')
@section('content')

    <div class="right_panel">
        <div class="white_box_outer">

            <div class="heading_holder value_span9">
                <span class="lft">Add SMS Client </span>
            </div>

            <div class="white_box value_span8">


                <form action="/sms/client/create" method="post">
                    {{csrf_field()}}


                    <div class="form-group">
                        <label for="user_id">Affiliate:</label>
                        <select class="form-control" name="user_id" id="user_id" style="width:200px;">
                            @foreach($users as $user)
                                <option value="{{$user->idrep}}">{{$user->user_name}}</option>
                            @endforeach
                        </select>
                        <p>
                            <span class="small_txt value_span10">Note: Please make sure to add the country calling code to the front of the number (<b>+1</b> (555)-555-5555) </span>
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="phoneNumber">Service Phone Number:</label>
                        <input class="form-control" type="text" name="phoneNumber" id="phoneNumber"
                               style="width:200px;">
                    </div>
                    <button class="btn btn-primary ">Create SMS Account</button>
                    <p>
                        <span class="small_txt value_span10">Note: The affiliate will need the SMS Chat permission to actually use it.</span>
                    </p>
                </form>

            </div>
        </div>
    </div>


@endsection
