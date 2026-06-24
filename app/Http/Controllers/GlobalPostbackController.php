<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalPostbackController extends Controller
{
    public function show()
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        $postbackUrl = DB::table('user_postbacks')
            ->where('user_id', '=', $currentUserContext->id)
            ->value('url');

        return view('account.global-postback', [
            'postbackUrl' => old('postback_url', (string) $postbackUrl),
        ]);
    }

    public function update(Request $request)
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
        ]);

        DB::table('user_postbacks')->updateOrInsert(
            ['user_id' => $currentUserContext->id],
            [
                'url' => trim((string) ($validated['postback_url'] ?? '')),
                'free_sign_up_url' => '',
            ]
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
