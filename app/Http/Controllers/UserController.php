<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\User;
use App\Click;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Paginate;
use Illuminate\Support\Facades\Cache;
use LeadMax\TrackYourStats\Table\Date;

class UserController extends Controller
{

    public function viewManagersAffiliates($id)
    {
        $manager = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->findOrFail($id);


        $affiliates = $manager->users()->withRole(Privilege::ROLE_AFFILIATE)->with('referrer');

        $paginate = new Paginate(request('rpp',10), $affiliates->count());

        $affiliates = $affiliates->paginate(request('rpp', 10));

        return view('user.managers-affiliates', compact('manager', 'affiliates','paginate'));
    }

    public function viewManageUsers()
    {

	    $userType = Session::userType();
	    $canViewUsers = Session::permissions()->can('view_all_users');

        $this->validate(request(), [
            'showInactive' => 'numeric|min:0|max:1'
        ]);

	    $users =
		    ($userType == Privilege::ROLE_ADMIN && $canViewUsers) || $userType == Privilege::ROLE_GOD ?
			    User::withRole(request('role', Privilege::ROLE_AFFILIATE))->with('referrer')
			    :
			    User::myUsers()->withRole(request('role', Privilege::ROLE_AFFILIATE))->with('referrer');

        if (request('showInactive', 0) == 1) {
            $users->where('status', 0);
        } else {
            $users->where('status', 1);
        }
/*
		if (Session::userType() == Privilege::ROLE_ADMIN && (request('role') == null ||  request('role') == '3')) {
			$userId = Session::userID();
			$managers = DB::table('rep')->where('referrer_repid', '=', $userId)->get()->pluck('idrep')->toArray();
			$users->whereIn('referrer_repid', $managers);
		}
		*/
        $users = $users->get();
		$users = $this->getDiffForHumans($users);

        return view('user.manage', compact('users'));
    }

	public function AuthRouteAPI(Request $request){
		return $request->user();
	}

	public function blockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->insert([
			'rep_idrep' => $userID,
			'sub_id'    => $subID,
		]);

		return response()->json(['success' => true]);
	}

	public function unblockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->where('rep_idrep', '=', $userID)->where('sub_id', '=', $subID)->delete();

		return response()->json(['success' => true]);
	}

	public function getUserSubIds() {
        $affId = $_GET["idrep"] ?? null;
		$data = DB::select(
			"SELECT
	        sub_ids.sub_id as subId,
	        CASE WHEN blocked_sub_ids.sub_id IS NULL THEN FALSE ELSE TRUE END AS blocked
		     FROM sub_ids
		     LEFT JOIN blocked_sub_ids ON blocked_sub_ids.sub_id = sub_ids.sub_id
		     WHERE sub_ids.idrep = ?
		     GROUP BY subId", [ $affId ]
		);
		return json_encode($data);
    }

	public function changeAffPayout(Request $request) {
		$message = null;

		$userID = $request->rep;
		$offer = $request->offer_id;
		$payout = $request->payout;

		// TODO: check if already has access or not.

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			$offerAccess = DB::table('rep_has_offer')
			                 ->where('rep_idrep', '=', $userID)
			                 ->where('offer_idoffer', '=', $offer)->get();
			if (count($offerAccess) > 0) {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)
				  ->update([
					  'payout' => $payout
				  ]);
				$success = true;
			} else {
				$success = false;
				$message = "User does not have access to offer yet!";
			}

		} else {
			$success = false;
			$message = "You don't have permissions to do this!";
		}
		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function updateAffOfferAccess(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$access = $request->access;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			if ($access) {
				DB::table('rep_has_offer')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'payout'        => $request->payout
				]);
			} else {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)->delete();
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function editUserOffers(User $user) {
		$userID = $user->idrep;
		$userFName = $user->first_name;

		$offers = DB::table('offer')->where('status', '=', 1)->select('idoffer', 'offer_name', 'payout')->get()->toArray();

		foreach($offers as $index => $offer ) {
			$affHasOffer = DB::table('rep_has_offer')->where('rep_idrep', '=', $userID)->where('offer_idoffer', '=', $offer->idoffer)->get()->toArray();

			if (count($affHasOffer) > 0) {
				$offers[$index]->has_offer = true;
				$offers[$index]->reppayout = $affHasOffer[0]->payout;
			} else {
				$offers[$index]->has_offer = false;
				$offers[$index]->reppayout = 1.00;
			}
			$offers[$index]->idrep = $userID;
		}


		return view('user.offers')->with(['offers' => $offers, 'name' => $userFName]);
	}

	public function enableUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$status = $request->status;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();

			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'status' => $status
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'status'        => $status
				]);
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);

	}

	public function setUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$cap = $request->cap;
		$message = "";
		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();
			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'cap' => $cap
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'cap' => $cap
				]);
			}

			$success = true;

		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

	private function getDiffForHumans($users) {

		foreach($users as $key => $user) {
			if($user->rep_timestamp) {
				$user->rep_timestamp = Carbon::parse($user->rep_timestamp)->diffForHumans();
			}
		}

		return $users;
	}
}
