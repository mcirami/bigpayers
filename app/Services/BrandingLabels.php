<?php

namespace App\Services;

class BrandingLabels
{
    public static function account(): string
    {
        return (string) config('branding.account.singular');
    }

    public static function accounts(): string
    {
        return (string) config('branding.account.plural');
    }

    public static function affiliate(): string
    {
        return (string) config('branding.affiliate.singular');
    }

    public static function affiliates(): string
    {
        return (string) config('branding.affiliate.plural');
    }

    public static function viewData(): array
    {
        return [
            'accountTypeLabel' => self::account(),
            'accountTypeLabelPlural' => self::accounts(),
            'affiliateTypeLabel' => self::affiliate(),
            'affiliateTypeLabelPlural' => self::affiliates(),
        ];
    }
}
