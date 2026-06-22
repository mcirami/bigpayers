<?php

namespace App\Services;

class SmsApiEndpoint
{
    public static function baseUrl(): string
    {
        return rtrim((string) config('services.sms.base_url'), '/');
    }

    public static function url(string $path): string
    {
        return self::baseUrl() . '/' . ltrim($path, '/');
    }
}
