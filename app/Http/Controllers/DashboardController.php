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
        $currentUserContext = CurrentUserSession::snapshot();
        $currentUser = $currentUserContext->data;

        $with = [
            'canViewPostback' => $currentUserContext->can(Permissions::VIEW_POSTBACK),
            'company' => $company,
            'currentUser' => $currentUser,
            'postBackURL' => getWebRoot()."?uid=".$company->getUID()."&clickid=",
            'userId' => $currentUserContext->id,
            'firstName' => $currentUser->first_name,
            'email' => $currentUser->email,
	        'userType' => $currentUserContext->type,
	        'domain' => RequestContext::schemeAndHttpHost() . "/signup?mid=",
        ];

        return view('home', $with);
    }

}
