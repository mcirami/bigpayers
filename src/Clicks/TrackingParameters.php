<?php

namespace LeadMax\TrackYourStats\Clicks;

class TrackingParameters
{
    private const PUBLIC_KEY_MAP = [
        'repid' => 'rid',
        'offerid' => 'oid',
        'sub1' => 's1',
        'sub2' => 's2',
        'sub3' => 's3',
        'sub4' => 's4',
        'sub5' => 's5',
    ];

    public static function get(array $params, string $legacyKey, $default = null)
    {
        if (array_key_exists($legacyKey, $params) && $params[$legacyKey] !== null && $params[$legacyKey] !== '') {
            return $params[$legacyKey];
        }

        $publicKey = self::publicKey($legacyKey);
        if (array_key_exists($publicKey, $params) && $params[$publicKey] !== null && $params[$publicKey] !== '') {
            return $params[$publicKey];
        }

        return $default;
    }

    public static function has(array $params, string $legacyKey): bool
    {
        return self::get($params, $legacyKey) !== null;
    }

    public static function normalize(array $params): array
    {
        foreach (self::PUBLIC_KEY_MAP as $legacyKey => $publicKey) {
            if (!array_key_exists($legacyKey, $params) && array_key_exists($publicKey, $params)) {
                $params[$legacyKey] = $params[$publicKey];
            }
        }

        return $params;
    }

    public static function publicKey(string $legacyKey): string
    {
        return self::PUBLIC_KEY_MAP[$legacyKey] ?? $legacyKey;
    }
}
