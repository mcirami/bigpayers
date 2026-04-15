<?php

namespace App\Http\Controllers;

use App\EmailPool;
use App\User;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class EmailPoolController extends Controller
{

    public function showAffiliateEmailPools()
    {
        $affiliate = User::find(Session::userID());


        $availablePools = EmailPool::availablePools()->get();

        $ownedPools = $affiliate->emailPools()->get();

        return view('emails.emailpools', compact('availablePools', 'ownedPools'));
    }

    public function claimEmailPool($id)
    {
        $pool = EmailPool::availablePools()->where('id', '=', $id)->first();

        if (is_null($pool)) {
            abort(404);
        }

        $user = User::find(Session::userID());


        if ($pool->canAffiliateClaimPool($user->idrep)) {
            $pool->affiliate()->attach($user);
        } else {
            return back()->withErrors(['Cannot claim this email pool. You are limited to one new pool per day! You can claim older pools instead.']);
        }

        return back();
    }

    public function downloadEmailPool($id)
    {
        $pool = EmailPool::where('id', '=', $id)->with('emails')->first();

        $affiliate = User::find(Session::userID());

        if (!$affiliate->emailPools()->find($pool->id)) {
            abort(403, 'You do not own this pool!');
        }

        $contents = "";
        foreach ($pool->emails as $email) {
            $contents .= $email->email."\r\n";
        }
        \Storage::disk('local')->put("emailPools/email_pool_{$pool->id}.txt", $contents);

        return response()->download(\Storage::disk('local')->path("emailPools/email_pool_{$pool->id}.txt"))->deleteFileAfterSend(true);
    }

}
