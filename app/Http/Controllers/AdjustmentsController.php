<?php

namespace App\Http\Controllers;

use App\Privilege;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Offer\AdjustmentsLog;
use LeadMax\TrackYourStats\System\Session;

class AdjustmentsController extends Controller
{


    public function showAddSaleLog()
    {
        return view('salelog.add', [
            'defaultTimestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i'),
            'affiliateLabel' => config('branding.affiliate.singular'),
            'affiliatePluralLabel' => config('branding.affiliate.plural'),
        ]);
    }

    public function getAffiliates()
    {
        return Session::user()
            ->users()
            ->withRole(Privilege::ROLE_AFFILIATE)
            ->select('idrep as id', 'user_name as name')
            ->where('rep.status', '=', 1)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getAffiliatesOffers($id)
    {
        $user = \App\User::myUsers()->where('idrep', '=', $id)->first();


        return $user->offers()->select('idoffer as id', 'offer_name as name')->where('offer.status', '=',
            1)->orderBy('idoffer', 'DESC')->get();
    }

    public function createSale(Request $request)
    {

        $request->validate([
            'affiliate' => 'required|numeric',
            'offer' => 'required|numeric',
            'date' => 'required',
            'customPayout' => 'numeric',
        ]);




        $click = new Click();
        $click->rep_idrep = $request->get('affiliate');
        $click->offer_idoffer = $request->get('offer');
        $click->first_timestamp = $request->get('date');
        $click->ip_address = $_SERVER["SERVER_ADDR"];
        $click->browser_agent = "TYS_GENERATED";
        $click->click_type = Click::TYPE_GENERATED;
        $click->save();


        $customPayout = $request->get('customPayout') !== null ? $request->get('customPayout') : false;
        $conversion = new Conversion();
        $conversion->timestamp = $request->date;
        $conversion->click_id = $click->id;

        if ($customPayout) {
            $conversion->paid = $customPayout;
        }

        $conversion->registerSale();

        $log = new AdjustmentsLog($conversion->id, Session::userID());
        $log->setAction(AdjustmentsLog::ACTION_CREATE_SALE);
        $log->log();


        return redirect('/report/adjustments');
    }

}
