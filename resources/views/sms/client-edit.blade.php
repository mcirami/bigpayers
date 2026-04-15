@extends('layouts.master')
@section('content')
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9">
                <span class="lft">Edit SMS Client </span>
            </div>
            <div class="white_box value_span8">
                <form action="/sms/client/update" method="post">
                    {{csrf_field()}}
                    <input type="hidden" name="id" value="{{$smsClient->id}}">
                    <div class="form-group">
                        <label class="" for="username">Username (Email)</label>
                        <input value="{{$smsClient->username}}" class="form-control" type="text"
                               name="username" id="username"
                               style="width:400px">
                    </div>
                    <div class="form-group">
                        <label class="" for="password">Password</label>
                        <input value="{{$smsClient->password}}" class="form-control" type="text"
                               name="password" id="password"
                               style="width:400px">
                    </div>
                    <button class="btn btn-primary ">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection