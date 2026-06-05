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
            'Intentionally unrouted legacy files are not registered as Laravel routes.',
            $output
        );
        $this->assertStringContainsString(
            'Modern views and assets do not reference retired legacy script endpoints.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy POST compatibility routes have CSRF exceptions.',
            $output
        );
        $this->assertStringContainsString(
            'Registered PHP compatibility routes map to legacy files or documented public exceptions.',
            $output
        );
        $this->assertStringContainsString(
            'Documented non-legacy PHP compatibility route exceptions remain registered.',
            $output
        );
        $this->assertStringContainsString(
            'Allowed public PHP entrypoints exist and have documented reasons.',
            $output
        );
        $this->assertStringContainsString(
            'Runtime code does not reference the retired legacy company session loader.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code sends legacy mail through LegacyMail.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code reads legacy permission metadata through LegacyPermissions.',
            $output
        );
    }

    public function test_audit_summary_uses_current_inventory_counts(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyFiles = $this->invokeAuditMethod($command, 'legacyPhpFiles');
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');

        $this->assertSame(Command::SUCCESS, Artisan::call('legacy:audit-fallback-coverage'));
        $output = Artisan::output();

        $this->assertStringContainsString("Audited {$legacyFiles->count()} legacy PHP files.", $output);
        $this->assertStringContainsString(
            ($legacyFiles->count() - count($intentionallyUnrouted)) . ' files have explicit Laravel route coverage.',
            $output
        );
        $this->assertStringContainsString(
            count($intentionallyUnrouted) . ' files are intentionally unrouted support or retired script files.',
            $output
        );
    }

    public function test_intentionally_unrouted_legacy_files_have_documented_reasons(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyFiles = $this->invokeAuditMethod($command, 'legacyPhpFiles');
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');
        $inventoryErrors = $this->invokeAuditMethod($command, 'intentionallyUnroutedInventoryErrors', [$legacyFiles]);

        $this->assertNotEmpty($intentionallyUnrouted);
        $this->assertTrue($inventoryErrors->isEmpty(), $inventoryErrors->implode('; '));

        foreach ($intentionallyUnrouted as $legacyFile => $reason) {
            $this->assertFileExists(base_path('legacy/' . $legacyFile));
            $this->assertIsString($reason);
            $this->assertNotSame('', trim($reason));
        }
    }

    public function test_intentionally_unrouted_inventory_errors_report_stale_or_blank_entries(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyFiles = $this->invokeAuditMethod($command, 'legacyPhpFiles');
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');
        $intentionallyUnrouted['404.php'] = '';
        $intentionallyUnrouted['missing_legacy_file.php'] = '';
        $this->setAuditProperty($command, 'intentionallyUnrouted', $intentionallyUnrouted);

        $errors = $this->invokeAuditMethod($command, 'intentionallyUnroutedInventoryErrors', [$legacyFiles]);

        $this->assertContains(
            '404.php: intentionally unrouted reason is blank.',
            $errors->all()
        );
        $this->assertContains(
            'missing_legacy_file.php: listed as intentionally unrouted but legacy/missing_legacy_file.php does not exist.',
            $errors->all()
        );
        $this->assertContains(
            'missing_legacy_file.php: intentionally unrouted reason is blank.',
            $errors->all()
        );
    }

    public function test_registered_route_reader_sees_representative_compatibility_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('routeUrisFromRegisteredRoutes');
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

    public function test_registered_route_reader_ignores_api_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');

        $this->assertNotContains('api/user', $routeUris);
        $this->assertNotContains('api/sms-orders', $routeUris);
        $this->assertContains('login.php', $routeUris);
    }

    public function test_intentionally_unrouted_files_are_not_registered_as_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
        $errors = $this->invokeAuditMethod($command, 'intentionallyUnroutedRouteErrors', [$routeUris]);

        $this->assertTrue($errors->isEmpty(), $errors->implode('; '));
    }

    public function test_registered_php_routes_map_to_legacy_files_or_documented_public_exceptions(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyFiles = $this->invokeAuditMethod($command, 'legacyPhpFiles');
        $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
        $errors = $this->invokeAuditMethod($command, 'registeredPhpRouteInventoryErrors', [$legacyFiles, $routeUris]);

        $this->assertTrue($errors->isEmpty(), $errors->implode('; '));
    }

    public function test_registered_php_route_inventory_errors_report_unknown_php_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyFiles = collect([
            'login.php',
        ]);
        $routeUris = [
            'login.php',
            'alogin.php',
            'css/company.php',
            'missing_compatibility.php',
        ];

        $errors = $this->invokeAuditMethod($command, 'registeredPhpRouteInventoryErrors', [$legacyFiles, $routeUris]);

        $this->assertContains(
            'missing_compatibility.php: registered PHP route has no matching legacy file or documented public exception.',
            $errors->all()
        );
        $this->assertNotContains(
            'css/company.php: registered PHP route has no matching legacy file or documented public exception.',
            $errors->all()
        );
        $this->assertNotContains(
            'alogin.php: registered PHP route has no matching legacy file or documented public exception.',
            $errors->all()
        );
    }

    public function test_allowed_non_legacy_php_route_inventory_errors_report_stale_or_blank_entries(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $allowedNonLegacyPhpRoutes = $this->auditProperty($command, 'allowedNonLegacyPhpRoutes');
        $allowedNonLegacyPhpRoutes['alogin.php'] = '';
        $allowedNonLegacyPhpRoutes['missing_public_compatibility.php'] = '';
        $this->setAuditProperty($command, 'allowedNonLegacyPhpRoutes', $allowedNonLegacyPhpRoutes);

        $errors = $this->invokeAuditMethod(
            $command,
            'allowedNonLegacyPhpRouteInventoryErrors',
            [['alogin.php', 'css/company.php', 'login_themes/{theme}/index.php']]
        );

        $this->assertContains(
            'alogin.php: documented non-legacy PHP route exception reason is blank.',
            $errors->all()
        );
        $this->assertContains(
            'missing_public_compatibility.php: documented non-legacy PHP route exception is not registered.',
            $errors->all()
        );
        $this->assertContains(
            'missing_public_compatibility.php: documented non-legacy PHP route exception reason is blank.',
            $errors->all()
        );
    }

    public function test_public_php_inventory_has_no_unexpected_entrypoints(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $unexpectedPublicPhp = $this->invokeAuditMethod($command, 'unexpectedPublicPhpEntrypoints');

        $this->assertTrue($unexpectedPublicPhp->isEmpty(), $unexpectedPublicPhp->implode('; '));
    }

    public function test_allowed_public_php_entrypoints_exist_and_have_documented_reasons(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $errors = $this->invokeAuditMethod($command, 'allowedPublicPhpInventoryErrors');

        $this->assertTrue($errors->isEmpty(), $errors->implode('; '));
    }

    public function test_allowed_public_php_inventory_errors_report_stale_or_blank_entries(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $allowedPublicPhp = $this->auditProperty($command, 'allowedPublicPhp');
        $allowedPublicPhp['index.php'] = '';
        $allowedPublicPhp['missing_public_entrypoint.php'] = '';
        $this->setAuditProperty($command, 'allowedPublicPhp', $allowedPublicPhp);

        $errors = $this->invokeAuditMethod($command, 'allowedPublicPhpInventoryErrors');

        $this->assertContains(
            'public/index.php: allowed public PHP entrypoint reason is blank.',
            $errors->all()
        );
        $this->assertContains(
            'public/missing_public_entrypoint.php: allowed public PHP entrypoint does not exist.',
            $errors->all()
        );
        $this->assertContains(
            'public/missing_public_entrypoint.php: allowed public PHP entrypoint reason is blank.',
            $errors->all()
        );
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
            'retiredCompanySessionDependencyErrors',
            'legacyPermissionsDependencyErrors',
            'legacyMailDependencyErrors',
            'legacyPostCsrfExceptionErrors',
            'registeredPhpRouteInventoryErrors',
            'allowedNonLegacyPhpRouteInventoryErrors',
            'allowedPublicPhpInventoryErrors',
        ] as $methodName) {
            $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
            $arguments = match ($methodName) {
                'registeredPhpRouteInventoryErrors' => [
                    $this->invokeAuditMethod($command, 'legacyPhpFiles'),
                    $routeUris,
                ],
                'allowedNonLegacyPhpRouteInventoryErrors' => [$routeUris],
                default => [],
            };
            $errors = $this->invokeAuditMethod($command, $methodName, $arguments);

            $this->assertTrue(
                $errors->isEmpty(),
                "{$methodName} reported errors: " . $errors->implode('; ')
            );
        }
    }

    public function test_legacy_post_csrf_exception_errors_report_missing_and_stale_entries(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $legacyPostRoutes = collect([
            'signup.php',
            'missing_exception.php',
        ]);
        $csrfExceptions = [
            'signup.php',
            'stale_exception.php',
        ];

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPostCsrfExceptionErrorsFor',
            [$legacyPostRoutes, $csrfExceptions]
        );

        $this->assertContains(
            'missing_exception.php: POST compatibility route is missing from VerifyCsrfToken exceptions.',
            $errors->all()
        );
        $this->assertContains(
            'stale_exception.php: VerifyCsrfToken exception does not match a registered POST compatibility route.',
            $errors->all()
        );
    }

    public function test_retired_company_session_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'retiredCompanySessionDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\Company;',
                'src/BadHelper.php' => '$company = Company::loadFromSession();',
                'app/Http/Controllers/CleanController.php' => 'use App\\Company;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Company instead of the legacy company class.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadHelper.php: Use App\\Company current-company helpers instead of the legacy session company loader.',
            $errors->all()
        );
        $this->assertCount(2, $errors);
    }

    public function test_legacy_session_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySessionDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\Session;',
                'src/BadHelper.php' => '\\LeadMax\\TrackYourStats\\System\\Session::userID();',
                'app/Support/CurrentUserSession.php' => 'use LeadMax\\TrackYourStats\\System\\Session;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\CurrentUserSession;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\CurrentUserSession instead of importing the legacy session class directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadHelper.php: Use App\\Support\\CurrentUserSession instead of importing the legacy session class directly.',
            $errors->all()
        );
        $this->assertCount(2, $errors);
    }

    public function test_legacy_mail_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyMailDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\Mail;',
                'app/Support/LegacyMail.php' => 'use LeadMax\\TrackYourStats\\System\\Mail;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyMail as Mail;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyMail instead of importing the legacy mail class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_permissions_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPermissionsDependencyErrorsFor',
            [[
                'routes/web.php' => 'use LeadMax\\TrackYourStats\\User\\Permissions;',
                'app/Support/LegacyPermissions.php' => 'use LeadMax\\TrackYourStats\\User\\Permissions;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyPermissions as Permissions;',
            ]]
        );

        $this->assertContains(
            'routes/web.php: Use App\\Support\\LegacyPermissions instead of importing the legacy permissions class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_audit_hardening_pattern_lists_have_documented_messages(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        foreach ([
            'frontControllerForbiddenPatterns',
            'legacyBootstrapRequiredPatterns',
            'legacyBootstrapForbiddenPatterns',
            'retiredCompanySessionForbiddenPatterns',
            'legacySessionForbiddenPatterns',
            'legacyPermissionsForbiddenPatterns',
            'legacyMailForbiddenPatterns',
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

    private function invokeAuditMethod(AuditLegacyFallbackCoverage $command, string $methodName, array $arguments = [])
    {
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($command, $arguments);
    }

    private function auditProperty(AuditLegacyFallbackCoverage $command, string $propertyName): array
    {
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($command);
    }

    private function setAuditProperty(AuditLegacyFallbackCoverage $command, string $propertyName, array $value): void
    {
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($command, $value);
    }
}
