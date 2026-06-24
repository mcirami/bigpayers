<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\LegacyPostBackUrl as PostBackUrl;
use Illuminate\Http\Request;

class GlobalPostbackController extends Controller
{
    public function show()
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        $postbackUrl = new PostBackUrl($currentUserContext->id);

        return view('account.global-postback', [
            'postbackUrl' => old('postback_url', (string) $postbackUrl->getGlobalPostBackURL(PostBackUrl::GLOBAL_CONVERSION_URL)),
        ]);
    }

    public function update(Request $request)
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
        ]);

        PostBackUrl::updateUserPostBacks(
            $currentUserContext->id,
            trim((string) ($validated['postback_url'] ?? ''))
        );

        return redirect('/global-postback')->with('message', 'Global postback updated successfully.');
    }

    private function ensureAffiliateAccess(): CurrentUserContext
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->type === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');

        return $currentUserContext;
    }
}
