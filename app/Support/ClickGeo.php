<?php

namespace App\Support;

use App\Services\GeoIpDatabase;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use MaxMind\Db\Reader\InvalidDatabaseException;

class ClickGeo
{
    protected static ?Reader $reader = null;

    /**
     * @throws InvalidDatabaseException
     */
    protected static function reader(): Reader
    {
        if (self::$reader === null) {
            self::$reader = new Reader(GeoIpDatabase::configuredPath());
        }

        return self::$reader;
    }

    public static function findGeo($ip): array
    {
        if ($ip === '') {
            return self::unknownGeo();
        }

        return Cache::remember("geoip_{$ip}", now()->addDays(7), function () use ($ip) {
            try {
                $record = self::reader()->city($ip);

                if ($record->country->isoCode === '') {
                    return self::unknownGeo();
                }

                return [
                    'isoCode' => $record->country->isoCode,
                    'subDivision' => $record->mostSpecificSubdivision->name,
                    'city' => $record->city->name,
                    'postal' => $record->postal->code,
                    'latitude' => $record->location->latitude,
                    'longitude' => $record->location->longitude,
                ];
            } catch (\Exception $exception) {
                return self::unknownGeo();
            }
        });
    }

    private static function unknownGeo(): array
    {
        return [
            'isoCode' => 'UNKNOWN',
            'subDivision' => 'UNKNOWN',
            'city' => 'UNKNOWN',
            'postal' => 'UNKNOWN',
            'latitude' => 'UNKNOWN',
            'longitude' => 'UNKNOWN',
        ];
    }
}
