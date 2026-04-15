@extends('layouts.master')

@section('content')
    <div id="error_message">
        <svg  style="color: red" width="34" height="34" viewBox="0 0 24 24" fill="red" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16zM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12z" fill="red"/>
            <path d="M12 14a1 1 0 0 1-1-1V7a1 1 0 1 1 2 0v6a1 1 0 0 1-1 1zm-1.5 2.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0z" fill="red"/>
        </svg>
        <p></p>
    </div>
    <div id="user_info" class = "right_panel edit_user_offers">
        <div class = "heading_holder value_span9">
            <span class = "lft">{{$name}}'s Offer's</span>
        </div>
        <div class = "white_box_outer">
            <div class="rounded mx-auto mt-10 columns-1">
                <div class="white_box manage_aff value_span8">
                    <table class="table_01   large_table" id="mainTable">
                        <thead>
                        <tr>
                            <th class=\"value_span9\">Offer ID</th>
                            <th class=\"value_span9\">Offer Name</th>
                            <th class=\"value_span9\">Offer Payout</th>

                            @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_aff_payout"))
                                <th class=\"value_span9\">Change Aff Payout</th>
                            @endif

                            @if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_aff_payout"))
                                <th class=\"value_span9\">Offer Access</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($offers as $offer)
                            <tr>
                                <td>{{$offer->idoffer}}</td>
                                <td>{{$offer->offer_name}}</td>
                                <td>{{$offer->payout}}</td>
                                <td>
                                    <input
                                            class="update_aff_payout"
                                            style="width:100px;"
                                            type="number"
                                            step="0.25"
                                            id="offer_{{$offer->idoffer}}"
                                            data-offer="{{$offer->idoffer}}"
                                            data-rep="{{$offer->idrep}}"
                                            value="{{$offer->reppayout}}"
                                    />
                                </td>

                                @php $hasAccess = $offer->has_offer ? "checked" : ""; @endphp
                                <td class="offer_access">
                                    <input
                                            class="offer_access_check"
                                            type="checkbox"
                                            id="offer_access"
                                            data-rep="{{$offer->idrep}}"
                                            data-offer="{{$offer->idoffer}}"
                                            name="offer_access"
                                            {{$hasAccess}}>
                                    <label for="offer_access">Allow Access</label>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection