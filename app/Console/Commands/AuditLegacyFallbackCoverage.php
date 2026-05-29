<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

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

    private array $allowedPublicPhp = [
        'index.php' => 'Laravel front controller.',
    ];

    private array $retiredScriptEndpoints = [
        'scripts/affiliate_signup.php' => 'Use the Laravel signup routes.',
        'scripts/offer/request_offer.php' => 'Use /offer/{id}/request.',
        'scripts/offer/rules/device/add.php' => 'Use POST /offer/rules/device.',
        'scripts/offer/rules/device/edit.php' => 'Use GET|POST /offer/rules/device/{rule}.',
        'scripts/offer/rules/geo/addGeo.php' => 'Use POST /offer/rules/geo.',
        'scripts/offer/rules/geo/editGeo.php' => 'Use GET|POST /offer/rules/geo/{rule}.',
        'scripts/process_bonuses.php' => 'Use /bonuses/process.',
        'scripts/sale_log.php' => 'Use the Laravel chat-log routes.',
        'scripts/update_geoip.php' => 'Use the provisioning/ops workflow; the web updater is retired.',
    ];

    private array $frontControllerForbiddenPatterns = [
        '../legacy' => 'public/index.php must not include files from the legacy directory.',
        'legacy/index.php' => 'public/index.php must not execute legacy/index.php.',
        'is_file($file)' => 'public/index.php must not dynamically check request paths for executable files.',
        'include($file)' => 'public/index.php must not dynamically include request-matched files.',
    ];

    private array $legacyBootstrapRequiredPatterns = [
        'BIGPAYERS_LEGACY_LOADER_BOOTSTRAPPED' => 'bootstrap/legacy_loader.php is missing the idempotency guard.',
        'require_once __DIR__. "/../vendor/autoload.php";' => 'bootstrap/legacy_loader.php must load Composer with require_once.',
        'session_status() === PHP_SESSION_NONE' => 'bootstrap/legacy_loader.php must guard native session startup.',
    ];

    private array $legacyBootstrapForbiddenPatterns = [
        'include __DIR__. "/../vendor/autoload.php";' => 'bootstrap/legacy_loader.php must not include Composer repeatedly.',
    ];

    public function handle(): int
    {
        $legacyFiles = $this->legacyPhpFiles();
        $routeUris = $this->routeUrisFromRegisteredRoutes();

        $unexpectedIntentionalRoutes = $this->intentionallyUnroutedRouteErrors($routeUris);

        if ($unexpectedIntentionalRoutes->isNotEmpty()) {
            $this->error('Legacy files marked intentionally unrouted are registered as Laravel routes:');
            $unexpectedIntentionalRoutes->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $intentionalInventoryErrors = $this->intentionallyUnroutedInventoryErrors($legacyFiles);

        if ($intentionalInventoryErrors->isNotEmpty()) {
            $this->error('Intentionally unrouted legacy file inventory is stale or incomplete:');
            $intentionalInventoryErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $missing = $this->legacyFilesWithoutRouteCoverage($legacyFiles, $routeUris);
        $unexpectedPublicPhp = $this->unexpectedPublicPhpEntrypoints();

        if ($missing->isNotEmpty()) {
            $this->error('Legacy PHP files without explicit route coverage or an intentional unrouted reason:');
            $missing->each(fn ($file) => $this->line(" - {$file}"));

            return self::FAILURE;
        }

        if ($unexpectedPublicPhp->isNotEmpty()) {
            $this->error('Unexpected public PHP entrypoints:');
            $unexpectedPublicPhp->each(fn ($file) => $this->line(" - public/{$file}"));

            return self::FAILURE;
        }

        $rewriteErrors = $this->publicRewriteHardeningErrors();

        if ($rewriteErrors->isNotEmpty()) {
            $this->error('Public rewrite hardening is incomplete:');
            $rewriteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $frontControllerErrors = $this->frontControllerFallbackErrors();

        if ($frontControllerErrors->isNotEmpty()) {
            $this->error('Front controller fallback hardening is incomplete:');
            $frontControllerErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyBootstrapErrors = $this->legacyBootstrapHardeningErrors();

        if ($legacyBootstrapErrors->isNotEmpty()) {
            $this->error('Legacy bootstrap hardening is incomplete:');
            $legacyBootstrapErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $modernScriptReferenceErrors = $this->modernRetiredScriptReferenceErrors();

        if ($modernScriptReferenceErrors->isNotEmpty()) {
            $this->error('Modern app code still references retired legacy script endpoints:');
            $modernScriptReferenceErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $this->info("Audited {$legacyFiles->count()} legacy PHP files.");
        $this->info(($legacyFiles->count() - count($this->intentionallyUnrouted)) . ' files have explicit Laravel route coverage.');
        $this->info(count($this->intentionallyUnrouted) . ' files are intentionally unrouted support or retired script files.');
        $publicEntrypointCount = count($this->allowedPublicPhp);
        $publicEntrypointSummary = $publicEntrypointCount === 1
            ? '1 public PHP entrypoint is an expected front controller or compatibility redirect.'
            : "{$publicEntrypointCount} public PHP entrypoints are expected front controllers or compatibility redirects.";
        $this->info($publicEntrypointSummary);
        $this->info('Public webserver rewrites route direct PHP file requests through Laravel.');
        $this->info('Front controller has no dynamic legacy file fallback.');
        $this->info('Legacy bootstrap is idempotent and guards native session startup.');
        $this->info('Intentionally unrouted legacy files are not registered as Laravel routes.');
        $this->info('Modern views and assets do not reference retired legacy script endpoints.');

        return self::SUCCESS;
    }

    private function legacyPhpFiles()
    {
        return collect(File::allFiles(base_path('legacy')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->sort()
            ->values();
    }

    private function routeUrisFromRegisteredRoutes(): array
    {
        return collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('web', (array) $route->getAction('middleware'), true))
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function legacyFilesWithoutRouteCoverage($legacyFiles, array $routeUris)
    {
        return $legacyFiles
            ->reject(fn ($file) => in_array($file, $routeUris, true))
            ->reject(fn ($file) => array_key_exists($file, $this->intentionallyUnrouted))
            ->values();
    }

    private function intentionallyUnroutedInventoryErrors($legacyFiles)
    {
        $legacyFileLookup = $legacyFiles->flip();

        return collect($this->intentionallyUnrouted)
            ->flatMap(function ($reason, $file) use ($legacyFileLookup) {
                $errors = [];

                if (!$legacyFileLookup->has($file)) {
                    $errors[] = "{$file}: listed as intentionally unrouted but legacy/{$file} does not exist.";
                }

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "{$file}: intentionally unrouted reason is blank.";
                }

                return $errors;
            })
            ->values();
    }

    private function intentionallyUnroutedRouteErrors(array $routeUris)
    {
        return collect(array_keys($this->intentionallyUnrouted))
            ->filter(fn ($file) => in_array($file, $routeUris, true))
            ->map(fn ($file) => "{$file}: {$this->intentionallyUnrouted[$file]}")
            ->values();
    }

    private function unexpectedPublicPhpEntrypoints()
    {
        return collect(File::allFiles(public_path()))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace('\\', '/', $file->getRelativePathname()))
            ->reject(fn ($file) => array_key_exists($file, $this->allowedPublicPhp))
            ->sort()
            ->values();
    }

    private function publicRewriteHardeningErrors()
    {
        $errors = collect();
        $htaccess = public_path('.htaccess');
        $webConfig = public_path('web.config');

        if (!File::exists($htaccess) || !str_contains(File::get($htaccess), 'Route Direct PHP Entrypoints Through Laravel')) {
            $errors->push('public/.htaccess is missing the direct PHP entrypoint rewrite rule.');
        }

        if (!File::exists($webConfig) || !str_contains(File::get($webConfig), 'Route Direct PHP Files Through Laravel')) {
            $errors->push('public/web.config is missing the direct PHP entrypoint rewrite rule.');
        }

        return $errors;
    }

    private function frontControllerFallbackErrors()
    {
        $errors = collect();
        $frontController = public_path('index.php');

        if (!File::exists($frontController)) {
            return $errors->push('public/index.php is missing.');
        }

        $contents = File::get($frontController);

        $this->appendForbiddenPatternErrors($errors, $contents, $this->frontControllerForbiddenPatterns);

        return $errors;
    }

    private function legacyBootstrapHardeningErrors()
    {
        $errors = collect();
        $legacyBootstrap = base_path('bootstrap/legacy_loader.php');

        if (!File::exists($legacyBootstrap)) {
            return $errors->push('bootstrap/legacy_loader.php is missing.');
        }

        $contents = File::get($legacyBootstrap);

        $this->appendMissingPatternErrors($errors, $contents, $this->legacyBootstrapRequiredPatterns);
        $this->appendForbiddenPatternErrors($errors, $contents, $this->legacyBootstrapForbiddenPatterns);

        return $errors;
    }

    private function modernRetiredScriptReferenceErrors()
    {
        $errors = collect();
        $directories = [
            'resources/views',
            'resources/assets',
            'public/js',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['js', 'php'], true)) {
                    continue;
                }

                $contents = File::get($file->getPathname());
                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                foreach ($this->retiredScriptEndpoints as $endpoint => $replacement) {
                    if (str_contains($contents, $endpoint)) {
                        $errors->push("{$relativePath} references {$endpoint}. {$replacement}");
                    }
                }
            }
        }

        return $errors;
    }

    private function appendMissingPatternErrors($errors, string $contents, array $requiredPatterns): void
    {
        foreach ($requiredPatterns as $pattern => $message) {
            if (!str_contains($contents, $pattern)) {
                $errors->push($message);
            }
        }
    }

    private function appendForbiddenPatternErrors($errors, string $contents, array $forbiddenPatterns): void
    {
        foreach ($forbiddenPatterns as $pattern => $message) {
            if (str_contains($contents, $pattern)) {
                $errors->push($message);
            }
        }
    }
}
