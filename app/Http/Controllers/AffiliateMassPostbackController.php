<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\Support\CurrentUserContext;
use App\Support\CurrentUserSession;
use App\Support\OfferDomain\Offer as OfferSupport;
use App\Support\OfferDomain\RepHasOffer;
use Illuminate\Http\Request;
use PDO;

class AffiliateMassPostbackController extends Controller
{
    public function show()
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        return view('account.mass-postback', [
            'offers' => $this->ownedOffers($currentUserContext),
        ]);
    }

    public function update(Request $request)
    {
        $currentUserContext = $this->ensureAffiliateAccess();

        $validated = $request->validate([
            'postback_url' => 'nullable|string|max:255',
            'offerList' => 'required|array|min:1',
            'offerList.*' => 'integer',
        ]);

        $ownedOfferIds = $this->ownedOffers($currentUserContext)
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
            $currentUserContext->id,
            $offerIds
        );

        if (!$updated) {
            return back()
                ->withInput()
                ->withErrors('Unable to assign the postback URL. Please try again.');
        }

        return redirect('/account/mass-postback')->with('message', 'Postback URL assigned successfully.');
    }

    private function ownedOffers(?CurrentUserContext $currentUserContext = null)
    {
        $currentUserContext ??= CurrentUserSession::snapshot();

        return collect(
            OfferSupport::selectOwnedOffers($currentUserContext->type)->fetchAll(PDO::FETCH_OBJ)
        )->values();
    }

    private function ensureAffiliateAccess(): CurrentUserContext
    {
        $currentUserContext = CurrentUserSession::snapshot();
        abort_unless($currentUserContext->type === Privilege::ROLE_AFFILIATE, 403, 'Incorrect user type');

        return $currentUserContext;
    }
}
