<?php

namespace App\Services;

class SmsPoolConfig
{
    public static function baseUrl(): string
    {
        return rtrim((string) config('services.smspool.base_url'), '/');
    }

    public static function apiKey(): string
    {
        return (string) config('services.smspool.key');
    }

    public static function url(string $endpoint): string
    {
        return self::baseUrl() . '/' . ltrim($endpoint, '/');
    }
}
