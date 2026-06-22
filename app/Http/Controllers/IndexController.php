<?php

namespace App\Http\Controllers;

use App\Company;
use App\Support\LegacyClickRegistrationEvent as ClickRegistrationEvent;
use App\Support\LegacyIPBlackList as IPBlackList;
use App\Support\LegacyLander as Lander;
use App\Support\LegacyPostBackURLEventHandler as PostBackURLEventHandler;
use App\Support\LegacyTrackingParameters as TrackingParameters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{


    public function index(Request $request)
    {
        $company = $this->currentCompany();
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
        if ($company->isCompanyOfferUrl($request->getHttpHost())) {
            return redirect('404');
        }

        if (config('database.connections.mysql.database') != "chattrackpro") {
            if ($request->getHttpHost() !== $company->landing_page && $request->getHttpHost() !== $company->login_url) {
                if ($company->getSubDomain() == "debug") {
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
            $company = $this->currentCompany();

            $blacklist = new IPBlackList($request->ip());
            if ($blacklist->isBlackListed()) {
                $blacklist->logIP();
            }

            if ($request->get("uid") !== $company->getUID()) {
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

    private function currentCompany(): Company
    {
        $company = Company::instance()->first();

        abort_unless($company, 404, 'Company install not found.');

        return $company;
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

        $ip = $this->clientIp($request);

        $clickRegistrationEvent = new ClickRegistrationEvent($repId, $offerId, $trackingQuery, $ip);
        if ( ! $clickRegistrationEvent->fire()) {
            return redirect('404');
        }
    }

    private function clientIp(Request $request): string
    {
        $ip = $request->server('HTTP_CLIENT_IP')
            ?: $request->server('HTTP_X_FORWARDED_FOR')
            ?: $request->server('REMOTE_ADDR', $request->ip());

        return explode(',', (string) $ip)[0];
    }

}
