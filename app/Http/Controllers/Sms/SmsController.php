<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Support\CurrentUserSession;
use Illuminate\Support\ViewErrorBag;

class SmsController extends Controller
{

    public function getChattingPage()
    {
        $smsClient = CurrentUserSession::user()->smsClients()->first();


        if (is_null($smsClient)) {
            return back()->withErrors('You don\'t have an SMS Client! Please ask your manager for one');
        }


        return view('sms.main')->with(['userId' => CurrentUserSession::id()]);
    }
}
