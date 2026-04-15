<?php

namespace App\Http\Traits;

use App\ClickGeoCache;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\User\Permissions;

trait ClickTraits {

	/**
	 * Apply the default formatting for the Query results, used by between(..)
	 * This is public so if the consumer wishes to modify the original query(..), they can and will be able to use
	 * the default formatting.
	 * @param object $results
	 * @return object
	 */
	public function formatResults(object $results): object {
		$per = Permissions::loadFromSession();
		$geoCacheByIp = $this->loadGeoCacheByIp($results);
		$resolvedGeoByIp = [];

		if ($per->can("view_fraud_data")) {
			foreach ($results as $row => $val) {

				if ($val->isoCode) {
					if (isset($geoCacheByIp[$val->ip_address])) {
						$geo = $geoCacheByIp[$val->ip_address];
					} else {
						$geo = $this->resolveGeoByIp($val->ip_address, $resolvedGeoByIp);
					}
				} else {
					$geo = $this->resolveGeoByIp($val->ip_address, $resolvedGeoByIp);
				}

				foreach ($geo as $key => $val2) {
					$val->$key = $val2;
				}
			}
		} else {
			foreach ($results as $row => $val) {
				$geo = $this->resolveGeoByIp($val->ip_address, $resolvedGeoByIp, $geoCacheByIp);
				$val->isoCode = $geo["isoCode"] ?? $geo["country_code"] ?? null;
				unset($val->ip);
			}
		}

		return $results;
	}

	private function loadGeoCacheByIp(object $results): array
	{
		$ips = [];

		foreach ($results as $result) {
			if (!empty($result->ip_address)) {
				$ips[] = $result->ip_address;
			}
		}

		$ips = array_values(array_unique($ips));

		if (empty($ips)) {
			return [];
		}

		return ClickGeoCache::query()
		                    ->whereIn('ip_address', $ips)
		                    ->get()
		                    ->keyBy('ip_address')
		                    ->map(function ($row) {
			                    return $row->toArray();
		                    })
		                    ->toArray();
	}

	private function resolveGeoByIp(?string $ipAddress, array &$resolvedGeoByIp, ?array $geoCacheByIp = null): array
	{
		if (empty($ipAddress)) {
			return [];
		}

		if ($geoCacheByIp !== null && isset($geoCacheByIp[$ipAddress])) {
			return $geoCacheByIp[$ipAddress];
		}

		if (!isset($resolvedGeoByIp[$ipAddress])) {
			$resolvedGeoByIp[$ipAddress] = ClickGeo::findGeo($ipAddress);
		}

		return $resolvedGeoByIp[$ipAddress];
	}
}
