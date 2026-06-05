<?php

namespace App\Console\Commands;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;

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

    private array $allowedNonLegacyPhpRoutes = [
        'alogin.php' => 'Legacy admin-login alias redirecting to /login/{id}.',
        'css/company.php' => 'Public compatibility redirect to /css/company.css.',
        'login_themes/{theme}/index.php' => 'Public compatibility redirect to /login.',
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

    private array $retiredCompanySessionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Company' => 'Use App\\Company instead of the legacy company class.',
        'Company::loadFromSession()' => 'Use App\\Company current-company helpers instead of the legacy session company loader.',
    ];

    private array $legacySessionForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Session' => 'Use App\\Support\\CurrentUserSession instead of importing the legacy session class directly.',
    ];

    private array $legacySessionAllowedFiles = [
        'app/Support/CurrentUserSession.php' => 'The dedicated boundary around the legacy session class.',
    ];

    private array $legacyPermissionsForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\User\\Permissions' => 'Use App\\Support\\LegacyPermissions instead of importing the legacy permissions class directly.',
    ];

    private array $legacyPermissionsAllowedFiles = [
        'app/Support/LegacyPermissions.php' => 'The dedicated boundary around the legacy permissions class.',
    ];

    private array $legacyClickGeoForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\Clicks\\ClickGeo' => 'Use App\\Support\\LegacyClickGeo instead of importing the legacy ClickGeo class directly.',
    ];

    private array $legacyClickGeoAllowedFiles = [
        'app/Support/LegacyClickGeo.php' => 'The dedicated boundary around the legacy ClickGeo class.',
    ];

    private array $legacyMailForbiddenPatterns = [
        'LeadMax\\TrackYourStats\\System\\Mail' => 'Use App\\Support\\LegacyMail instead of importing the legacy mail class directly.',
    ];

    private array $legacyMailAllowedFiles = [
        'app/Support/LegacyMail.php' => 'The dedicated boundary around the legacy mail class.',
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

        $registeredPhpRouteErrors = $this->registeredPhpRouteInventoryErrors($legacyFiles, $routeUris);

        if ($registeredPhpRouteErrors->isNotEmpty()) {
            $this->error('Registered PHP compatibility routes are stale or undocumented:');
            $registeredPhpRouteErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $nonLegacyPhpRouteErrors = $this->allowedNonLegacyPhpRouteInventoryErrors($routeUris);

        if ($nonLegacyPhpRouteErrors->isNotEmpty()) {
            $this->error('Documented non-legacy PHP compatibility routes are stale or incomplete:');
            $nonLegacyPhpRouteErrors->each(fn ($error) => $this->line(" - {$error}"));

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

        $publicPhpEntrypointErrors = $this->allowedPublicPhpInventoryErrors();

        if ($publicPhpEntrypointErrors->isNotEmpty()) {
            $this->error('Allowed public PHP entrypoint inventory is stale or incomplete:');
            $publicPhpEntrypointErrors->each(fn ($error) => $this->line(" - {$error}"));

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

        $retiredCompanySessionDependencyErrors = $this->retiredCompanySessionDependencyErrors();

        if ($retiredCompanySessionDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still references retired legacy company session dependencies:');
            $retiredCompanySessionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacySessionDependencyErrors = $this->legacySessionDependencyErrors();

        if ($legacySessionDependencyErrors->isNotEmpty()) {
            $this->error('Runtime code still imports the legacy session class directly:');
            $legacySessionDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyPermissionsDependencyErrors = $this->legacyPermissionsDependencyErrors();

        if ($legacyPermissionsDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy permissions class directly:');
            $legacyPermissionsDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyClickGeoDependencyErrors = $this->legacyClickGeoDependencyErrors();

        if ($legacyClickGeoDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy ClickGeo class directly:');
            $legacyClickGeoDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $legacyMailDependencyErrors = $this->legacyMailDependencyErrors();

        if ($legacyMailDependencyErrors->isNotEmpty()) {
            $this->error('Modern Laravel code still imports the legacy mail class directly:');
            $legacyMailDependencyErrors->each(fn ($error) => $this->line(" - {$error}"));

            return self::FAILURE;
        }

        $csrfExceptionErrors = $this->legacyPostCsrfExceptionErrors();

        if ($csrfExceptionErrors->isNotEmpty()) {
            $this->error('Legacy POST compatibility CSRF exceptions are missing or stale:');
            $csrfExceptionErrors->each(fn ($error) => $this->line(" - {$error}"));

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
        $this->info('Allowed public PHP entrypoints exist and have documented reasons.');
        $this->info('Modern views and assets do not reference retired legacy script endpoints.');
        $this->info('Legacy POST compatibility routes have CSRF exceptions.');
        $this->info('Registered PHP compatibility routes map to legacy files or documented public exceptions.');
        $this->info('Documented non-legacy PHP compatibility route exceptions remain registered.');
        $this->info('Runtime code does not reference the retired legacy company session loader.');
        $this->info('Runtime code reads current user/session state through CurrentUserSession.');
        $this->info('Modern Laravel code reads legacy permission metadata through LegacyPermissions.');
        $this->info('Modern Laravel code resolves legacy ClickGeo through LegacyClickGeo.');
        $this->info('Modern Laravel code sends legacy mail through LegacyMail.');

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

    private function registeredPhpRouteInventoryErrors($legacyFiles, array $routeUris)
    {
        $legacyFileLookup = $legacyFiles->flip();

        return collect($routeUris)
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->reject(fn ($uri) => $legacyFileLookup->has($uri))
            ->reject(fn ($uri) => array_key_exists($uri, $this->allowedNonLegacyPhpRoutes))
            ->map(fn ($uri) => "{$uri}: registered PHP route has no matching legacy file or documented public exception.")
            ->values();
    }

    private function allowedNonLegacyPhpRouteInventoryErrors(array $routeUris)
    {
        return collect($this->allowedNonLegacyPhpRoutes)
            ->flatMap(function ($reason, $uri) use ($routeUris) {
                $errors = [];

                if (!in_array($uri, $routeUris, true)) {
                    $errors[] = "{$uri}: documented non-legacy PHP route exception is not registered.";
                }

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "{$uri}: documented non-legacy PHP route exception reason is blank.";
                }

                return $errors;
            })
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

    private function allowedPublicPhpInventoryErrors()
    {
        return collect($this->allowedPublicPhp)
            ->flatMap(function ($reason, $file) {
                $errors = [];

                if (!File::exists(public_path($file))) {
                    $errors[] = "public/{$file}: allowed public PHP entrypoint does not exist.";
                }

                if (!is_string($reason) || trim($reason) === '') {
                    $errors[] = "public/{$file}: allowed public PHP entrypoint reason is blank.";
                }

                return $errors;
            })
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

    private function retiredCompanySessionDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->retiredCompanySessionDependencyErrorsFor($sourceFiles);
    }

    private function retiredCompanySessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->retiredCompanySessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacySessionDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
            'src',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacySessionDependencyErrorsFor($sourceFiles);
    }

    private function legacySessionDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacySessionAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacySessionForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyMailDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyMailDependencyErrorsFor($sourceFiles);
    }

    private function legacyMailDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyMailAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyMailForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPermissionsDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyPermissionsDependencyErrorsFor($sourceFiles);
    }

    private function legacyPermissionsDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyPermissionsAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyPermissionsForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyClickGeoDependencyErrors()
    {
        $sourceFiles = collect();
        $directories = [
            'app',
            'resources/views',
            'routes',
        ];

        foreach ($directories as $directory) {
            $path = base_path($directory);

            if (!File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $relativePath = $directory . '/' . str_replace('\\', '/', $file->getRelativePathname());

                if ($relativePath === 'app/Console/Commands/AuditLegacyFallbackCoverage.php') {
                    continue;
                }

                $sourceFiles[$relativePath] = File::get($file->getPathname());
            }
        }

        return $this->legacyClickGeoDependencyErrorsFor($sourceFiles);
    }

    private function legacyClickGeoDependencyErrorsFor($sourceFiles)
    {
        return collect($sourceFiles)
            ->reject(fn (string $contents, string $relativePath) => array_key_exists($relativePath, $this->legacyClickGeoAllowedFiles))
            ->flatMap(function (string $contents, string $relativePath) {
                $errors = [];

                foreach ($this->legacyClickGeoForbiddenPatterns as $pattern => $message) {
                    if (str_contains($contents, $pattern)) {
                        $errors[] = "{$relativePath}: {$message}";
                    }
                }

                return $errors;
            })
            ->values();
    }

    private function legacyPostCsrfExceptionErrors()
    {
        return $this->legacyPostCsrfExceptionErrorsFor(
            $this->legacyPostRouteUris(),
            $this->csrfExceptionUris()
        );
    }

    private function legacyPostRouteUris()
    {
        return collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('web', (array) $route->getAction('middleware'), true))
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->unique()
            ->sort()
            ->values();
    }

    private function legacyPostCsrfExceptionErrorsFor($legacyPostRoutes, array $csrfExceptions)
    {
        $normalizedCsrfExceptions = collect($csrfExceptions)
            ->map(fn ($uri) => trim($uri, '/'))
            ->unique()
            ->values();

        $missingExceptions = $legacyPostRoutes
            ->reject(fn ($uri) => $normalizedCsrfExceptions->contains($uri))
            ->map(fn ($uri) => "{$uri}: POST compatibility route is missing from VerifyCsrfToken exceptions.")
            ->values();

        $staleExceptions = $normalizedCsrfExceptions
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->reject(fn ($uri) => $legacyPostRoutes->contains($uri))
            ->map(fn ($uri) => "{$uri}: VerifyCsrfToken exception does not match a registered POST compatibility route.")
            ->values();

        return $missingExceptions
            ->merge($staleExceptions)
            ->values();
    }

    private function csrfExceptionUris(): array
    {
        $middleware = app(VerifyCsrfToken::class);
        $reflection = new ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        return collect($property->getValue($middleware))
            ->map(fn ($uri) => trim($uri, '/'))
            ->all();
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
