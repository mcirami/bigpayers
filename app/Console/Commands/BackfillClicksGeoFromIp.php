<?php

namespace App\Console\Commands;

use App\ClickGeoCache;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class BackfillClicksGeoFromIp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clicks:backfill-country-from-ip {--hours=24 : Number of hours to look back from now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill clicks.country_code from IP geo lookup and cache results in click_geo_cache.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $from = Carbon::now()->subHours($hours);

        $this->info("Processing clicks since {$from->toDateTimeString()} with NULL country_code...");

        $totalProcessed = 0;
        $totalUpdated = 0;
        $totalCached = 0;
        $totalCachePatched = 0;

        DB::table('clicks')
            ->select('idclicks', 'ip_address')
            ->whereNull('country_code')
            ->whereNotNull('ip_address')
            ->where('first_timestamp', '>=', $from)
            ->orderBy('idclicks')
            ->chunkById(500, function ($rows) use (&$totalProcessed, &$totalUpdated, &$totalCached, &$totalCachePatched) {
                foreach ($rows as $row) {
                    $totalProcessed++;

                    $geo = ClickGeo::findGeo($row->ip_address);
                    $isoCode = $geo['isoCode'] ?? null;

                    if (empty($isoCode)) {
                        continue;
                    }

                    $updated = DB::table('clicks')
                        ->where('idclicks', $row->idclicks)
                        ->whereNull('country_code')
                        ->update(['country_code' => $isoCode]);

                    $totalUpdated += $updated;

                    $cache = [
                        'ip_address' => $row->ip_address,
                        'country_code' => $isoCode,
                        'subDivision' => $geo['subDivision'] ?? null,
                        'city' => $geo['city'] ?? null,
                        'postal' => $geo['postal'] ?? null,
                        'latitude' => $geo['latitude'] ?? null,
                        'longitude' => $geo['longitude'] ?? null,
                        'resolved_at' => Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $created = ClickGeoCache::query()->firstOrCreate(
                        ['ip_address' => $row->ip_address],
                        $cache
                    );

                    if ($created->wasRecentlyCreated) {
                        $totalCached++;
                        continue;
                    }

                    $cacheUpdates = [];
                    foreach (['subDivision', 'city', 'postal', 'latitude', 'longitude'] as $field) {
                        if (is_null($created->{$field}) && !is_null($cache[$field])) {
                            $cacheUpdates[$field] = $cache[$field];
                        }
                    }

                    if (!empty($cacheUpdates)) {
                        $cacheUpdates['updated_at'] = Carbon::now();
                        ClickGeoCache::query()
                            ->whereKey($created->getKey())
                            ->update($cacheUpdates);
                        $totalCachePatched++;
                    }
                }
            }, 'idclicks');

        $this->info("Done. Processed: {$totalProcessed}, Clicks updated: {$totalUpdated}, Cache inserted: {$totalCached}, Cache patched: {$totalCachePatched}");

        return self::SUCCESS;
    }
}
