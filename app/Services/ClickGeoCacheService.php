<?php

namespace App\Services;

use App\ClickGeoCache;
use Illuminate\Support\Collection;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class ClickGeoCacheService {
	/**
	 * Ensure ClickGeoCache has geo rows for the given IPs.
	 */
	public function warm(Collection|array $ips): void
	{
		$ips = collect($ips)->filter()->unique()->values();

		if ($ips->isEmpty()) {
			return;
		}

		// Pull cached IPs as a collection so diff() stays collection-native.
		$ips->chunk(1000)->each(function ($chunk) {
			$cachedIps = ClickGeoCache::query()
			                          ->whereIn('ip_address', $chunk)
			                          ->pluck('ip_address');

			$missing = $chunk->diff($cachedIps);

			foreach ($missing as $ip) {
				$geo = ClickGeo::findGeo($ip);

				if (!empty($geo['isoCode'])) {
					ClickGeoCache::updateOrCreate(
						['ip_address' => $ip],
						[
							'country_code' => $geo['isoCode'],
							'subDivision' => $geo['subDivision'] ?? null,
							'city' => $geo['city'] ?? null,
							'postal' => $geo['postal'] ?? null,
							'latitude' => $geo['latitude'] ?? null,
							'longitude' => $geo['longitude'] ?? null,
							'resolved_at' => now(),
						]
					);
				}
			}
		});
	}
}
