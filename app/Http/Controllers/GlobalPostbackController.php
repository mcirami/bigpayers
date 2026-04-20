<?php

namespace App\Http\Controllers;

use App\Privilege;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\PostBackUrl;

class GlobalPostbackController extends Controller
{
    public function show()
    {
        $this->ensureAffiliateAccess();

        $postbackUrl = new PostBackUrl(Session::userID());

        return view('account.global-postback', [
            'postbackUrl' => old('postback_url', (string) $postbackUrl->getGlobalPostBackURL(PostBackUrl::GLOBAL_CONVERSION_URL)),
        ]);
    }

    public function update(Request $request)
    {
        $this->ensureAffiliateAccess();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
        ]);

        PostBackUrl::updateUserPostBacks(
            Session::userID(),
            trim((string) ($validated['postback_url'] ?? ''))
        );

        return redirect('/global-postback')->with('message', 'Global postback updated successfully.');
    }

    private function ensureAffiliateAccess(): void
    {
        abort_unless(Session::userType() === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');
    }
}
