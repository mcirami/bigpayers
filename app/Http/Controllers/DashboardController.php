<?php

namespace App\Http\Controllers;


use App\Company;
use App\Support\CurrentUserSession;
use App\Support\UserDomain\Permissions;

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
            'userId' => $currentUserContext->id,
            'firstName' => $currentUser->first_name,
            'email' => $currentUser->email,
	        'userType' => $currentUserContext->type,
        ];

        return view('home', $with);
    }

}
