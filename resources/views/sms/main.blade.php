@extends('layouts.master')
@section('content')

    <div class="container" id="app">
        <div class="row">

            <div class="col-lg-6">

                <conversation-box user-id="{{$userId}}"></conversation-box>


            </div>
            <div class="col-md-6">
                <chat-box></chat-box>
            </div>

        </div>
@endsection
