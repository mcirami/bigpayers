<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Services\BrandingLabels;
use App\Support\CurrentUserSession;
use App\Support\LegacyAdjustmentsLog as AdjustmentsLog;
use App\Support\LegacyClick as Click;
use App\Support\LegacyConversion as Conversion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdjustmentsController extends Controller
{


    public function showAddSaleLog()
    {
        return view('salelog.add', [
            'defaultTimestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i'),
            'affiliateLabel' => BrandingLabels::affiliate(),
            'affiliatePluralLabel' => BrandingLabels::affiliates(),
        ]);
    }

    public function getAffiliates()
    {
        return CurrentUserSession::user()
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
        $click->ip_address = $request->server('SERVER_ADDR', $request->ip());
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

        $log = new AdjustmentsLog($conversion->id, CurrentUserSession::id());
        $log->setAction(AdjustmentsLog::ACTION_CREATE_SALE);
        $log->log();


        return redirect('/report/adjustments');
    }

}
