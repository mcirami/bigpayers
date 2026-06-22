<?php

namespace App\Services;

use App\Company;

class SaleLogImageStorage
{
    public static function root(): string
    {
        return rtrim((string) config('filesystems.sale_log_directory'), '/');
    }

    public static function companyDirectory(?string $subDomain = null): string
    {
        return self::root() . '/' . ($subDomain ?: Company::currentSubDomain());
    }

    public static function saleLogDirectory($saleLogId, ?string $subDomain = null): string
    {
        return self::companyDirectory($subDomain) . "/{$saleLogId}";
    }

    public static function saleLogFilePath($saleLogId, string $fileName, ?string $subDomain = null): string
    {
        return self::saleLogDirectory($saleLogId, $subDomain) . '/' . basename($fileName);
    }
}
