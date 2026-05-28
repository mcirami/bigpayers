<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tests\TestCase;
use App\Console\Commands\AuditLegacyFallbackCoverage;

class LegacyFallbackAuditTest extends TestCase
{
    public function test_legacy_fallback_audit_passes(): void
    {
        $this->assertSame(Command::SUCCESS, Artisan::call('legacy:audit-fallback-coverage'));
        $output = Artisan::output();

        $this->assertStringContainsString(
            'Legacy bootstrap is idempotent and guards native session startup.',
            $output
        );
        $this->assertStringContainsString(
            'Modern views and assets do not reference retired legacy script endpoints.',
            $output
        );
    }

    public function test_intentionally_unrouted_legacy_files_have_documented_reasons(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty('intentionallyUnrouted');
        $property->setAccessible(true);

        $intentionallyUnrouted = $property->getValue($command);

        $this->assertNotEmpty($intentionallyUnrouted);

        foreach ($intentionallyUnrouted as $legacyFile => $reason) {
            $this->assertFileExists(base_path('legacy/' . $legacyFile));
            $this->assertIsString($reason);
            $this->assertNotSame('', trim($reason));
        }
    }

    public function test_route_parser_sees_representative_compatibility_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('routeUrisFromWebRoutes');
        $method->setAccessible(true);

        $routeUris = $method->invoke($command);

        foreach ([
            'login.php',
            'home.php',
            'offer_update.php',
            'scripts/process_bonuses.php',
            'scripts/update_geoip.php',
            'css/company.php',
            'login_themes/{theme}/index.php',
        ] as $expectedRoute) {
            $this->assertContains($expectedRoute, $routeUris);
        }
    }

    public function test_retired_script_endpoint_guard_targets_existing_or_routed_legacy_scripts(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty('retiredScriptEndpoints');
        $property->setAccessible(true);

        $retiredScriptEndpoints = $property->getValue($command);

        $this->assertNotEmpty($retiredScriptEndpoints);

        foreach ($retiredScriptEndpoints as $endpoint => $replacement) {
            $this->assertTrue(
                File::exists(base_path('legacy/' . $endpoint)) || str_contains(File::get(base_path('routes/web.php')), $endpoint),
                "{$endpoint} should be a legacy file or explicit compatibility route."
            );
            $this->assertIsString($replacement);
            $this->assertNotSame('', trim($replacement));
        }
    }

    public function test_audit_hardening_checks_are_individually_clean(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        foreach ([
            'publicRewriteHardeningErrors',
            'frontControllerFallbackErrors',
            'legacyBootstrapHardeningErrors',
            'modernRetiredScriptReferenceErrors',
        ] as $methodName) {
            $errors = $this->invokeAuditMethod($command, $methodName);

            $this->assertTrue(
                $errors->isEmpty(),
                "{$methodName} reported errors: " . $errors->implode('; ')
            );
        }
    }

    public function test_audit_hardening_pattern_lists_have_documented_messages(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        foreach ([
            'frontControllerForbiddenPatterns',
            'legacyBootstrapRequiredPatterns',
            'legacyBootstrapForbiddenPatterns',
        ] as $propertyName) {
            $patterns = $this->auditProperty($command, $propertyName);

            $this->assertNotEmpty($patterns);

            foreach ($patterns as $pattern => $message) {
                $this->assertIsString($pattern);
                $this->assertNotSame('', trim($pattern));
                $this->assertIsString($message);
                $this->assertNotSame('', trim($message));
            }
        }
    }

    public function test_front_controller_keeps_legacy_loader_without_dynamic_legacy_fallback(): void
    {
        $frontController = File::get(public_path('index.php'));

        $this->assertStringContainsString("require __DIR__.'/../bootstrap/legacy_loader.php';", $frontController);
        $this->assertStringNotContainsString('../legacy', $frontController);
        $this->assertStringNotContainsString('legacy/index.php', $frontController);
        $this->assertStringNotContainsString('is_file($file)', $frontController);
        $this->assertStringNotContainsString('include($file)', $frontController);
    }

    public function test_legacy_loader_stays_idempotent_and_guards_native_session_startup(): void
    {
        $legacyLoader = File::get(base_path('bootstrap/legacy_loader.php'));

        $this->assertStringContainsString('BIGPAYERS_LEGACY_LOADER_BOOTSTRAPPED', $legacyLoader);
        $this->assertStringContainsString('require_once __DIR__. "/../vendor/autoload.php";', $legacyLoader);
        $this->assertStringContainsString('session_status() === PHP_SESSION_NONE', $legacyLoader);
        $this->assertStringNotContainsString('include __DIR__. "/../vendor/autoload.php";', $legacyLoader);
    }

    public function test_public_webserver_configs_route_direct_php_requests_through_laravel(): void
    {
        $htaccess = File::get(public_path('.htaccess'));
        $webConfig = File::get(public_path('web.config'));

        $this->assertStringContainsString('Route Direct PHP Entrypoints Through Laravel', $htaccess);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/index\\.php$', $htaccess);
        $this->assertStringContainsString('RewriteRule ^.*\\.php$ index.php [L]', $htaccess);

        $this->assertStringContainsString('Route Direct PHP Files Through Laravel', $webConfig);
        $this->assertStringContainsString('<match url="^(?!index\\.php$).+\\.php$" ignoreCase="false" />', $webConfig);
        $this->assertStringContainsString('<action type="Rewrite" url="index.php" />', $webConfig);
    }

    private function invokeAuditMethod(AuditLegacyFallbackCoverage $command, string $methodName)
    {
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($command);
    }

    private function auditProperty(AuditLegacyFallbackCoverage $command, string $propertyName): array
    {
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($command);
    }
}
