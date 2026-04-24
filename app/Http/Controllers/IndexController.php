<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\PostBackURLEventHandler;
use LeadMax\TrackYourStats\Clicks\TrackingParameters;
use LeadMax\TrackYourStats\Clicks\URLEvents\ClickRegistrationEvent;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\IPBlackList;
use LeadMax\TrackYourStats\System\Lander;

class IndexController extends Controller
{


    public function index(Request $request)
    {
        $trackingQuery = TrackingParameters::normalize($request->query());

        if (
            TrackingParameters::get($trackingQuery, 'repid') &&
            TrackingParameters::get($trackingQuery, 'offerid')
        ) {
            return $this->clickRegistration($request);
        }

        if ($request->get('uid')) {
            return $this->postBackRegistration($request);
        }

        // if its an offer url, and there wasn't any parameters for posting or generating clicks..
        if (Company::loadFromSession()->isCompanyOfferUrl($request->getHttpHost()) == true) {
            return redirect('404');
        }

        if (Company::getSub() != "chattrackpro") {

            $company = Company::loadFromSession();

            if ($request->getHttpHost() !== $company->landing_page && $request->getHttpHost() !== $company->login_url) {
                if (Company::getCustomSub() == "debug") {
                    return redirect('login');
                }
            }

            $lander = new Lander($company);
            $lander->loadCompanyLander();
        }

        return view('landing-page');
    }


    public function postBackRegistration(Request $request)
    {
        if ($request->get('uid')) {

            $blacklist = new IPBlackList($request->ip());
            if ($blacklist->isBlackListed()) {
                $blacklist->logIP();
            }

            if ($request->get("uid") !== Company::loadFromSession()->getUID()) {
                return response()->json(['status' => 404, 'message' => 'Unknown UID.'], 404);
            }

            try {
                $handler = new PostBackURLEventHandler();

                return $handler->handleRequest();
            } catch (\Exception $e) {
                LogDB($e, null);

                return response()->json([
                    'status'  => 500,
                    'message' => $e->getMessage(),
                ], 500);
            }

        }

    }


    public function clickRegistration(Request $request)
    {
        $trackingQuery = TrackingParameters::normalize($request->query());
        $repId = TrackingParameters::get($trackingQuery, 'repid');
        $offerId = TrackingParameters::get($trackingQuery, 'offerid');
        $sub1 = TrackingParameters::get($trackingQuery, 'sub1');

        if (!$repId && !$offerId) {
            return redirect('404')->setStatusCode('404');
        }

	    if ($sub1) {
		    $subId = $sub1;

		    $blocked = DB::table( 'blocked_sub_ids' )
		                 ->where( 'rep_idrep', '=', $repId )
		                 ->where( 'sub_id', '=', $subId )
		                 ->distinct()->get()->pluck( 'sub_id' );
		    if ( ! $blocked->isEmpty() ) {
			    return redirect( '404' )->setStatusCode( '404' );
		    }
	    }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ( str_contains( $ip, ',' ) ) {
                $ip = substr($ip, 0, strpos($ip, ","));
            }
        }

        $clickRegistrationEvent = new ClickRegistrationEvent($repId, $offerId, $trackingQuery, $ip);
        if ( ! $clickRegistrationEvent->fire()) {
            return redirect('404');
        }
    }

}
