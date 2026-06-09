<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyPostBackUrl as PostBackUrl;
use Illuminate\Http\Request;

class GlobalPostbackController extends Controller
{
    public function show()
    {
        $this->ensureAffiliateAccess();

        $postbackUrl = new PostBackUrl(CurrentUserSession::id());

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
            CurrentUserSession::id(),
            trim((string) ($validated['postback_url'] ?? ''))
        );

        return redirect('/global-postback')->with('message', 'Global postback updated successfully.');
    }

    private function ensureAffiliateAccess(): void
    {
        abort_unless(CurrentUserSession::type() === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');
    }
}
