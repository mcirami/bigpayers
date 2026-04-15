@extends('layouts.master')

@section('content')

    <!--right_panel-->
    <div class="right_panel" id="app">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Create Offer</span></div>
            <div class="white_box value_span8">

                <form action="/offer/create" method="post" enctype="multipart/form-data">


                    {{csrf_field()}}

                    <div class="left_con01">
                        <p>
                            <label class="value_span9">Name</label>
                            <input id="offer_name" name="offer_name" type="text" value="" required/>
                        </p>


                        <p>
                            <label class="value_span9">Visibility</label>
                            <select name="is_public" id="is_public">
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PUBLIC}}">Public
                                </option>
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PRIVATE}}">Private
                                </option>
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_REQUESTABLE}}">
                                    Requestable
                                </option>
                            </select>
                        </p>


                        @isset($campaigns)
                            <p>
                                <label class="value_span9">Advertisers </label>
                                <select name="campaign" required>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{$campaign->id}}">{{$campaign->name}}</option>
                                    @endforeach
                                </select>
                            </p>
                        @endif

                        <p>
                            <label class="value_span9">Type</label>
                            <select class="form-control input-sm " id="offer_type" name="offer_type">
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::TYPE_CPC}}">CPA
                                </option>
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::TYPE_CPC}}">CPC</option>
                                <option value="{{\LeadMax\TrackYourStats\Offer\Offer::TYPE_PENDING_CONVERSION}}">Pending
                                    Conversion
                                </option>
                            </select>
                        </p>

                        <p>
                            <label class="value_span9">Status</label>
                            <select class="form-control input-sm " id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Disabled</option>
                            </select>
                        </p>

                        <p>
                            <label class="value_span9">Description</label>
                            <input type="text" class="form-control" name="description" maxlength="555" value=""
                                   id="description"/>
                        </p>

                        <p>
                            <label class="value_span9">URL</label>
                            <input type="text" class="form-control" name="url" maxlength="555" id="url" value=""
                                   required/>
                            <span class="small_txt value_span10">The offer URL where traffic will be directed to. The variables below can be used in offer URLs.</span>
                        </p>
                        <p>
                            When building offer url, these values will populate automatically:
                            <span class="small_txt value_span10">AffiliateID: #affid#</span>
                            <span class="small_txt value_span10">Username: #user#</span>
                            <span class="small_txt value_span10">Click ID: #clickid#</span>
                            <span class="small_txt value_span10">Offer ID: #offid#</span>
                        </p>
                        <p>
                            When storing values Sub ID 1-5 on incoming clicks, these tags will populate the
                            corresponding values.
                            <span class="small_txt value_span10">Sub ID 1: #sub1#</span>
                            <span class="small_txt value_span10">Sub ID 2: #sub2#</span>
                            <span class="small_txt value_span10">Sub ID 3: #sub3#</span>
                            <span class="small_txt value_span10">Sub ID 4: #sub4#</span>
                            <span class="small_txt value_span10">Sub ID 5: #sub5#</span>
                        </p>
                    </div>

                    <div class="right_con01">
                        <p>
                            <label class="value_span9">Payout</label>
                            <input type="text" name="payout" maxlength="12" value="" id="payout" required/>
                            <span class="small_txt value_span10">The Amount paid to affiliates per conversion</span>
                        </p>


                        <p>
                            <label class="value_span9">Offer Cap</label>
                            {{--<offer-cap-crud></offer-cap-crud>--}}
                            <input class="fixCheckBox" type="checkbox" id="enable_cap" name="enable_cap"> Offer Cap
                        <p id="offer_cap_form" style="display:none;">
                            <span class="small_txt value_span10">Cap Type</span>
                            <select id="cap_type" name="cap_type">
                                <option value="click">Click</option>
                                <option value="conversion">Conversion</option>
                            </select>

                            <span class="small_txt value_span10">Cap Interval</span>
                            <select id="cap_interval" name="cap_interval">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="total">Total</option>
                            </select>

                            <span class="small_txt value_span10">Interval Cap</span>
                            {{--<input type="number" name="cap_num" value="" id="cap_num" required/>--}}
                            <span class="small_txt value_span10">Offer Redirect on Cap</span>
                            <select></select>
                        </p>


                        <p>
                            <radio>Account Roles</radio>
                            @php
                                $create->printRadios();
                            @endphp
                        </p>

                        <p>
                            <user-offer-assignment></user-offer-assignment>
                        </p>
                    </div>

                    <span class="btn_yellow"> <input type="submit" name="button"
                                                     class="value_span6-2 value_span2 value_span1-2"
                                                     value="Create"/></span>
                </form>


            </div>
        </div>


        <!--right_panel-->

@endsection

@section('footer')


@endsection
