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

        $with = [
            'canViewPostback' => Session::permissions()->can(Permissions::VIEW_POSTBACK),
            'company' => $company,
            'postBackURL' => getWebRoot()."?uid=".$company->getUID()."&clickid=",
            'userId' => Session::userID(),
            'firstName' => Session::userData()->first_name,
            'email' => Session::userData()->email,
	        'userType' => Session::userType(),
	        'domain' => request()->getSchemeAndHttpHost() . "/signup?mid=",
        ];

        return view('home', $with);
    }

}
