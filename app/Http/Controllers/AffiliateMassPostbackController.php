<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyOffer;
use App\Support\LegacyRepHasOffer as RepHasOffer;
use Illuminate\Http\Request;
use PDO;

class AffiliateMassPostbackController extends Controller
{
    public function show()
    {
        $this->ensureAffiliateAccess();

        return view('account.mass-postback', [
            'offers' => $this->ownedOffers(),
        ]);
    }

    public function update(Request $request)
    {
        $this->ensureAffiliateAccess();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
            'offerList' => 'required|array|min:1',
            'offerList.*' => 'integer',
        ]);

        $ownedOfferIds = $this->ownedOffers()
            ->pluck('idoffer')
            ->map(fn ($offerId) => (int) $offerId)
            ->all();

        $offerIds = collect($validated['offerList'])
            ->map(fn ($offerId) => (int) $offerId)
            ->intersect($ownedOfferIds)
            ->values()
            ->all();

        abort_if(empty($offerIds), 403);

        $updated = RepHasOffer::assignPostBackToAffiliatesOffers(
            trim((string) ($validated['postback_url'] ?? '')),
            CurrentUserSession::id(),
            $offerIds
        );

        if (!$updated) {
            return back()
                ->withInput()
                ->withErrors('Unable to assign the postback URL. Please try again.');
        }

        return redirect('/account/mass-postback')->with('message', 'Postback URL assigned successfully.');
    }

    private function ownedOffers()
    {
        return collect(
            LegacyOffer::selectOwnedOffers(CurrentUserSession::type())->fetchAll(PDO::FETCH_OBJ)
        )->values();
    }

    private function ensureAffiliateAccess(): void
    {
        abort_unless(CurrentUserSession::type() === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');
    }
}
