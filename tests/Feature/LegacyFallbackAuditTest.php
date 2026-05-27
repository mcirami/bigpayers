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
        $this->assertStringContainsString(
            'Modern views and assets do not reference retired legacy script endpoints.',
            Artisan::output()
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
}
