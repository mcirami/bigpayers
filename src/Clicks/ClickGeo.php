<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/9/2017
 * Time: 12:14 PM
 */

namespace LeadMax\TrackYourStats\Clicks;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use MaxMind\Db\Reader\InvalidDatabaseException;

function unKnownGeo( array $geo ): array {
	$geo["isoCode"] = "UNKNOWN";

	$geo["subDivision"] = "UNKNOWN";

	$geo["city"] = "UNKNOWN";

	$geo["postal"] = "UNKNOWN";

	$geo["latitude"] = "UNKNOWN";

	$geo["longitude"] = "UNKNOWN";

	return $geo;
}

class ClickGeo
{

	/**
	 * Shared GeoIP reader instance so we do not pay the cost of reloading the
	 * MaxMind database for every lookup. The GeoIP2 reader is thread-safe for
	 * concurrent reads and dramatically faster when reused.
	 *
	 * @var Reader|null
	 */
	protected static ?Reader $reader = null;

	/**
	 * Lazily instantiate the GeoIP2 reader.
	 * @throws InvalidDatabaseException
	 */
	protected static function reader(): Reader
	{
		if (self::$reader === null) {
			self::$reader = new Reader(config('services.geo.ip_database'));
		}

		return self::$reader;
	}

    // INPUT: IP Address
    // OUTPUT: array with much geo info
    static function findGeo($ip)
    {
        $geo = array();
        if ($ip == "") {
	        return unKnownGeo($geo);
        }

        $cacheKey = "geoip_{$ip}";
        $ttl = now()->addDays(7);
        return Cache::remember($cacheKey, $ttl, function () use ($ip, $geo) {
            try {

                $reader = self::reader();
                $record = $reader->city($ip);
    
                 if($record->country->isoCode == "") {
                    return unKnownGeo($geo);
                }
    
                $geo["isoCode"] = $record->country->isoCode; // 'US'
    
                $geo["subDivision"] = $record->mostSpecificSubdivision->name;
    
                $geo["city"] = $record->city->name;
    
                $geo["postal"] = $record->postal->code;
    
                $geo["latitude"] = $record->location->latitude;
    
                $geo["longitude"] = $record->location->longitude;
            } catch (\Exception $e) {
    
                $geo = unKnownGeo($geo);
            }

            return $geo;
        });
    }
}