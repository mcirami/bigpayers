<?php

namespace LeadMax\TrackYourStats\Offer;

use App\Privilege;

class Payouts
{
    public static function resolveForRole(
        int $role,
        ?float $defaultPayout,
        ?float $affiliatePayout = null,
        ?float $managerPayout = null,
        ?float $adminPayout = null,
        ?float $customAffiliatePayout = null
    ): float {
        return match ($role) {
            Privilege::ROLE_ADMIN => (float) ($adminPayout ?? $defaultPayout ?? 0),
            Privilege::ROLE_MANAGER => (float) ($managerPayout ?? $defaultPayout ?? 0),
            Privilege::ROLE_AFFILIATE => (float) ($customAffiliatePayout ?? $affiliatePayout ?? $defaultPayout ?? 0),
            default => (float) ($defaultPayout ?? 0),
        };
    }

    public static function sqlForRole(
        int $role,
        string $offerAlias = 'offer',
        ?string $repHasOfferAlias = 'rep_has_offer'
    ): string {
        $defaultPayout = "{$offerAlias}.payout";

        return match ($role) {
            Privilege::ROLE_ADMIN => "COALESCE({$offerAlias}.admin_payout, {$defaultPayout}, 0)",
            Privilege::ROLE_MANAGER => "COALESCE({$offerAlias}.manager_payout, {$defaultPayout}, 0)",
            Privilege::ROLE_AFFILIATE => $repHasOfferAlias
                ? "COALESCE({$repHasOfferAlias}.payout, {$offerAlias}.affiliate_payout, {$defaultPayout}, 0)"
                : "COALESCE({$offerAlias}.affiliate_payout, {$defaultPayout}, 0)",
            default => "COALESCE({$defaultPayout}, 0)",
        };
    }
}
