<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditLegacyFallbackCoverage extends Command
{
    protected $signature = 'legacy:audit-fallback-coverage';

    protected $description = 'Verify legacy PHP files are explicitly routed or intentionally unrouted.';

    private array $intentionallyUnrouted = [
        '404.php' => 'Legacy error template, not a public workflow.',
        '500.php' => 'Legacy error template, not a public workflow.',
        'footer.php' => 'Legacy support include.',
        'header.php' => 'Legacy support include.',
        'index.php' => 'Root route is handled by Laravel.',
        'scripts/affiliate_signup.php' => 'Legacy AJAX endpoint used only by retired legacy signup form; Laravel signup routes are explicit.',
        'scripts/offer/request_offer.php' => 'Legacy offer request AJAX endpoint; modern offer request route is /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Legacy offer-rule AJAX endpoint replaced by /offer/rules/geo/{rule}.',
    ];

    public function handle(): int
    {
        $legacyFiles = collect(File::allFiles(base_path('legacy')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->sort()
            ->values();

        $routeUris = $this->routeUrisFromWebRoutes();

        $missing = $legacyFiles
            ->reject(fn ($file) => in_array($file, $routeUris, true))
            ->reject(fn ($file) => array_key_exists($file, $this->intentionallyUnrouted))
            ->values();

        if ($missing->isNotEmpty()) {
            $this->error('Legacy PHP files without explicit route coverage or an intentional unrouted reason:');
            $missing->each(fn ($file) => $this->line(" - {$file}"));

            return self::FAILURE;
        }

        $this->info("Audited {$legacyFiles->count()} legacy PHP files.");
        $this->info(($legacyFiles->count() - count($this->intentionallyUnrouted)) . ' files have explicit Laravel route coverage.');
        $this->info(count($this->intentionallyUnrouted) . ' files are intentionally unrouted support or retired script files.');

        return self::SUCCESS;
    }

    private function routeUrisFromWebRoutes(): array
    {
        $routes = File::get(base_path('routes/web.php'));

        preg_match_all(
            '/Route::(?:get|post|match|any|put|patch|delete)\(\s*(?:\[[^\]]+\]\s*,\s*)?[\'"]\/?([^\'"]+)[\'"]/',
            $routes,
            $matches
        );

        return collect($matches[1] ?? [])
            ->map(fn ($uri) => trim($uri, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
