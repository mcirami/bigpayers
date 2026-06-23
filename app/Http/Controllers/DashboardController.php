<?php

namespace App\Http\Controllers;


use App\Company;
use App\Support\CurrentUserSession;
use App\Support\LegacyPermissions as Permissions;
use App\Support\RequestContext;

class DashboardController extends Controller
{

    public function home()
    {
        $company = Company::instance()->first();
        abort_unless($company, 404, 'Company install not found.');
        $currentUser = CurrentUserSession::data();

        $with = [
            'canViewPostback' => CurrentUserSession::can(Permissions::VIEW_POSTBACK),
            'company' => $company,
            'currentUser' => $currentUser,
            'postBackURL' => getWebRoot()."?uid=".$company->getUID()."&clickid=",
            'userId' => CurrentUserSession::id(),
            'firstName' => $currentUser->first_name,
            'email' => $currentUser->email,
	        'userType' => CurrentUserSession::type(),
	        'domain' => RequestContext::schemeAndHttpHost() . "/signup?mid=",
        ];

        return view('home', $with);
    }

}
