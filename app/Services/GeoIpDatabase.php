<?php

namespace App\Services;

class GeoIpDatabase
{
    private const CANDIDATES = [
        'storage/GeoIP2-City.mmdb',
        'public/GeoIP2-City.mmdb',
        'resources/GeoIP2-City.mmdb',
    ];

    public static function configuredPath(): string
    {
        return (string) config('services.geo.ip_database');
    }

    public static function readablePath(string $root, string $default = 'resources/GeoIP2-City.mmdb'): string
    {
        $configuredPath = self::configuredPath();

        if ($configuredPath !== '' && is_readable($configuredPath)) {
            return $configuredPath;
        }

        foreach (self::CANDIDATES as $candidate) {
            $path = $root . DIRECTORY_SEPARATOR . $candidate;

            if (is_readable($path)) {
                return $path;
            }
        }

        return $default;
    }
}
