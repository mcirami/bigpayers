<?php

namespace App\Http\Controllers;


use App\Company;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;

class DashboardController extends Controller
{

    public function home()
    {
        $company = Company::instance()->first();
        abort_unless($company, 404, 'Company install not found.');
        $currentUser = Session::userData();

        $with = [
            'canViewPostback' => Session::permissions()->can(Permissions::VIEW_POSTBACK),
            'company' => $company,
            'currentUser' => $currentUser,
            'postBackURL' => getWebRoot()."?uid=".$company->getUID()."&clickid=",
            'userId' => Session::userID(),
            'firstName' => $currentUser->first_name,
            'email' => $currentUser->email,
	        'userType' => Session::userType(),
	        'domain' => request()->getSchemeAndHttpHost() . "/signup?mid=",
        ];

        return view('home', $with);
    }

}
