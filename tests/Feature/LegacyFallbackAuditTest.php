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
            'Laravel middleware initializes the legacy runtime boundary once per request.',
            $output
        );
        $this->assertStringContainsString(
            'Retired legacy marker URLs are blocked from modern views and assets.',
            $output
        );
        $this->assertStringContainsString(
            'Retired legacy PHP URLs are not registered as Laravel routes.',
            $output
        );
        $this->assertStringContainsString(
            'Modern views and assets do not reference retired legacy script endpoints.',
            $output
        );
        $this->assertStringContainsString(
            'Modern views and public assets do not reference legacy PHP compatibility URLs.',
            $output
        );
        $this->assertStringContainsString(
            'Retired legacy script endpoint URLs are blocked from modern source.',
            $output
        );
        $this->assertStringContainsString(
            'No legacy POST PHP routes or PHP CSRF exceptions remain registered.',
            $output
        );
        $this->assertStringContainsString(
            'No PHP compatibility routes remain registered.',
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
            'Modern Laravel code reads native PHP superglobals through NativeSession, NativeRequest, or request boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Runtime source reads environment-backed values through Laravel config.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy source classes read native PHP superglobals through NativeSession or NativeRequest boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code sends legacy mail through LegacyMail.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel source keeps direct legacy class references inside audited App\Support or bootstrap boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy support wrappers remain simple boundary aliases.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy support wrappers have specific audit allow-list coverage.',
            $output
        );
        $this->assertStringContainsString(
            'Support files with direct legacy references have specific audit allow-list coverage.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy boundary allow-list paths exist.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code reads legacy permission metadata through LegacyPermissions.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy ClickGeo through LegacyClickGeo.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy click writes through LegacyClick.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy click vars through LegacyClickVars.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy click search queries through LegacyClickSearcher.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy conversion helpers through LegacyConversion.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy pending conversion activation through LegacyPendingConversion.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code handles postback URL events through LegacyPostBackURLEventHandler.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code registers offer clicks through LegacyClickRegistrationEvent.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code encodes legacy click IDs through LegacyUid.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code normalizes tracking query parameters through LegacyTrackingParameters.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code loads legacy landers through LegacyLander.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code builds dashboard navigation through LegacyNavBar.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code manages IP blacklist records through LegacyIPBlackList.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code uploads sale-log images through LegacyImagesUploader.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code reads and sends notifications through LegacyNotifications.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy offer-domain helpers through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy offer support helpers through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy offer-rule helpers through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy payout helpers through LegacyPayouts.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code writes adjustment logs through LegacyAdjustmentsLog.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code writes sale logs through LegacySaleLog.',
            $output
        );
        $this->assertStringContainsString(
            'Runtime code resolves date helpers through App\\Support\\DateHelper.',
            $output
        );
        $this->assertStringContainsString(
            'Runtime code resolves pagination through App\\Support\\PaginationHelper.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy assignment helpers through LegacyAssignments.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code rebuilds user trees through LegacyTree.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy users through LegacyUser.',
            $output
        );
        $this->assertStringContainsString(
            'Modern login flows use LegacyLogin for legacy login constants.',
            $output
        );
        $this->assertStringContainsString(
            'Modern signup flows use LegacyAffiliateSignUp.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy user-domain helpers through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern offer postback URL flows use App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern layouts preserve admin-login state through Laravel request boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Legacy admin-login and notify classes are retired from runtime source.',
            $output
        );
        $this->assertStringContainsString(
            'Modern database update screens run through LegacyCompanyUpdater.',
            $output
        );
        $this->assertStringContainsString(
            'Modern Laravel code resolves legacy connections through LegacyConnection.',
            $output
        );
        $this->assertStringContainsString(
            'Modern report views render through LegacyReportHtml.',
            $output
        );
        $this->assertStringContainsString(
            'Modern report controllers coordinate reports through LegacyReporter.',
            $output
        );
        $this->assertStringContainsString(
            'Modern report controllers format reports through legacy report filter wrappers.',
            $output
        );
        $this->assertStringContainsString(
            'Modern payout reports use the audited legacy payout report boundary.',
            $output
        );
        $this->assertStringContainsString(
            'Modern report controllers resolve legacy database connections through LegacyDatabaseConnection.',
            $output
        );
        $this->assertStringContainsString(
            'Modern offer report controllers resolve legacy offer repositories through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern employee report controllers and commands resolve legacy employee repositories through App\Support boundaries.',
            $output
        );
        $this->assertStringContainsString(
            'Modern report controllers resolve remaining legacy report repositories through App\Support boundaries.',
            $output
        );
    }

    public function test_audit_summary_uses_current_inventory_counts(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');

        $this->assertSame(Command::SUCCESS, Artisan::call('legacy:audit-fallback-coverage'));
        $output = Artisan::output();

        $this->assertStringContainsString(
            'No legacy PHP files exist.',
            $output
        );
        $this->assertStringContainsString(
            count($intentionallyUnrouted) . ' retired legacy PHP URLs are documented.',
            $output
        );
    }

    public function test_intentionally_unrouted_legacy_urls_have_documented_reasons(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');
        $inventoryErrors = $this->invokeAuditMethod($command, 'intentionallyUnroutedInventoryErrors');

        $this->assertNotEmpty($intentionallyUnrouted);
        $this->assertTrue($inventoryErrors->isEmpty(), $inventoryErrors->implode('; '));

        foreach ($intentionallyUnrouted as $reason) {
            $this->assertIsString($reason);
            $this->assertNotSame('', trim($reason));
        }
    }

    public function test_intentionally_unrouted_inventory_errors_report_blank_entries(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $intentionallyUnrouted = $this->auditProperty($command, 'intentionallyUnrouted');
        $intentionallyUnrouted['404.php'] = '';
        $this->setAuditProperty($command, 'intentionallyUnrouted', $intentionallyUnrouted);

        $errors = $this->invokeAuditMethod($command, 'intentionallyUnroutedInventoryErrors');

        $this->assertContains(
            '404.php: intentionally unrouted reason is blank.',
            $errors->all()
        );
    }

    public function test_legacy_file_presence_errors_report_retired_php_files(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyFilePresenceErrors',
            [collect([
                'login.php',
                'scripts/affiliate_signup.php',
            ])]
        );

        $this->assertContains(
            'login.php: remove retired legacy PHP file.',
            $errors->all()
        );
        $this->assertContains(
            'scripts/affiliate_signup.php: remove retired legacy PHP file.',
            $errors->all()
        );
    }

    public function test_registered_route_reader_sees_representative_laravel_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('routeUrisFromRegisteredRoutes');
        $method->setAccessible(true);

        $routeUris = $method->invoke($command);

        foreach ([
            'click-id-tool',
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
        $this->assertContains('click-id-tool', $routeUris);
    }

    public function test_retired_legacy_php_urls_are_not_registered_as_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
        $errors = $this->invokeAuditMethod($command, 'intentionallyUnroutedRouteErrors', [$routeUris]);

        $this->assertTrue($errors->isEmpty(), $errors->implode('; '));
    }

    public function test_no_unexpected_registered_php_routes_remain(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
        $errors = $this->invokeAuditMethod($command, 'registeredPhpRouteInventoryErrors', [$routeUris]);

        $this->assertTrue($errors->isEmpty(), $errors->implode('; '));
    }

    public function test_registered_php_route_inventory_errors_report_unknown_php_routes(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $routeUris = [
            'login.php',
            'alogin.php',
            'css/company.php',
            'login_themes/{theme}/index.php',
            'missing_compatibility.php',
        ];

        $errors = $this->invokeAuditMethod($command, 'registeredPhpRouteInventoryErrors', [$routeUris]);

        $this->assertContains(
            'login.php: registered PHP route should be removed.',
            $errors->all()
        );
        $this->assertContains(
            'missing_compatibility.php: registered PHP route should be removed.',
            $errors->all()
        );
        $this->assertContains(
            'css/company.php: registered PHP route should be removed.',
            $errors->all()
        );
        $this->assertContains(
            'alogin.php: registered PHP route should be removed.',
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

    public function test_retired_script_endpoint_guard_has_documented_replacements(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty('retiredScriptEndpoints');
        $property->setAccessible(true);

        $retiredScriptEndpoints = $property->getValue($command);

        $this->assertNotEmpty($retiredScriptEndpoints);

        foreach ($retiredScriptEndpoints as $endpoint => $replacement) {
            $this->assertIsString($endpoint);
            $this->assertNotSame('', trim($endpoint));
            $this->assertIsString($replacement);
            $this->assertNotSame('', trim($replacement));
        }
    }

    public function test_modern_legacy_php_url_reference_errors_report_old_urls_in_views_and_assets(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'modernLegacyPhpUrlReferenceErrorsFor',
            [[
                'resources/views/bad.blade.php' => '<form action="/signup.php"></form>',
                'public/css/bad.css' => 'body { background-image: url("/offer_update.php?idoffer=1"); }',
                'resources/views/clean.blade.php' => '<form action="/signup"></form>',
            ]]
        );

        $this->assertContains(
            'resources/views/bad.blade.php: replace legacy PHP URL signup.php with its modern Laravel route.',
            $errors->all()
        );
        $this->assertContains(
            'public/css/bad.css: replace legacy PHP URL offer_update.php with its modern Laravel route.',
            $errors->all()
        );
        $this->assertCount(2, $errors);
    }

    public function test_audit_hardening_checks_are_individually_clean(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        foreach ([
            'publicRewriteHardeningErrors',
            'frontControllerFallbackErrors',
            'runtimeBootstrapHardeningErrors',
            'modernRetiredScriptReferenceErrors',
            'modernLegacyPhpUrlReferenceErrors',
            'retiredCompanySessionDependencyErrors',
            'legacyBoundaryDependencyErrors',
            'nativeSessionDependencyErrors',
            'legacySourceNativeSuperglobalErrors',
            'legacyPermissionsDependencyErrors',
            'legacyClickGeoDependencyErrors',
            'legacyClickDependencyErrors',
            'legacyClickVarsDependencyErrors',
            'legacyClickSearcherDependencyErrors',
            'legacyConversionDependencyErrors',
            'legacyPendingConversionDependencyErrors',
            'legacyPostBackUrlEventHandlerDependencyErrors',
            'legacyClickRegistrationEventDependencyErrors',
            'legacyUidDependencyErrors',
            'legacyTrackingParametersDependencyErrors',
            'legacyLanderDependencyErrors',
            'legacyNavBarDependencyErrors',
            'legacyIpBlackListDependencyErrors',
            'legacyImagesUploaderDependencyErrors',
            'legacyNotificationsDependencyErrors',
            'legacyOfferDomainDependencyErrors',
            'legacyOfferSupportDependencyErrors',
            'legacyOfferRulesDependencyErrors',
            'legacyPayoutsDependencyErrors',
            'legacyAdjustmentsLogDependencyErrors',
            'legacySaleLogDependencyErrors',
            'legacyDateDependencyErrors',
            'legacyPaginateDependencyErrors',
            'legacyAssignmentsDependencyErrors',
            'legacyTreeDependencyErrors',
            'legacyUserDependencyErrors',
            'legacyLoginDependencyErrors',
            'legacyAffiliateSignUpDependencyErrors',
            'legacyUserDomainDependencyErrors',
            'legacyOfferPostBackUrlDependencyErrors',
            'legacyAdminLoginDependencyErrors',
            'legacyNotifyDependencyErrors',
            'legacyCompanyUpdaterDependencyErrors',
            'legacyConnectionDependencyErrors',
            'legacyReportHtmlDependencyErrors',
            'legacyReporterDependencyErrors',
            'legacyReportFiltersDependencyErrors',
            'legacyReportObjectsDependencyErrors',
            'legacyDatabaseConnectionDependencyErrors',
            'legacyOfferReportRepositoriesDependencyErrors',
            'legacyEmployeeReportRepositoriesDependencyErrors',
            'legacyMiscReportRepositoriesDependencyErrors',
            'legacyMailDependencyErrors',
            'malformedLegacyNamespaceErrors',
            'legacySupportWrapperShapeErrors',
            'legacySupportWrapperInventoryErrors',
            'legacySupportDirectReferenceInventoryErrors',
            'legacyPostCsrfExceptionErrors',
            'registeredPhpRouteInventoryErrors',
            'allowedPublicPhpInventoryErrors',
            'boundaryAllowedPathInventoryErrors',
        ] as $methodName) {
            $routeUris = $this->invokeAuditMethod($command, 'routeUrisFromRegisteredRoutes');
            $arguments = match ($methodName) {
                'registeredPhpRouteInventoryErrors' => [$routeUris],
                default => [],
            };
            $errors = $this->invokeAuditMethod($command, $methodName, $arguments);

            $this->assertTrue(
                $errors->isEmpty(),
                "{$methodName} reported errors: " . $errors->implode('; ')
            );
        }
    }

    public function test_legacy_support_wrapper_shape_errors_report_non_alias_wrappers(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySupportWrapperShapeErrorsFor',
            [[
                'app/Support/LegacyClean.php' => <<<'PHP'
<?php

namespace App\Support;

use LeadMax\TrackYourStats\User\User;

class LegacyClean extends User
{
}
PHP,
                'app/Support/LegacyWrongNamespace.php' => <<<'PHP'
<?php

namespace App\Other;

use LeadMax\TrackYourStats\User\User;

class LegacyWrongNamespace extends User
{
}
PHP,
                'app/Support/LegacyWrongExtends.php' => <<<'PHP'
<?php

namespace App\Support;

use LeadMax\TrackYourStats\User\User;

class LegacyWrongExtends
{
}
PHP,
                'app/Support/LegacyBehavior.php' => <<<'PHP'
<?php

namespace App\Support;

use LeadMax\TrackYourStats\User\User;

class LegacyBehavior extends User
{
    public function extraBehavior()
    {
    }
}
PHP,
                'app/Support/LegacyMissingSource.php' => <<<'PHP'
<?php

namespace App\Support;

use LeadMax\TrackYourStats\User\MissingSource;

class LegacyMissingSource extends MissingSource
{
}
PHP,
            ]]
        );

        $this->assertContains(
            'app/Support/LegacyWrongNamespace.php: legacy support wrapper must live in the App\\Support namespace.',
            $errors->all()
        );
        $this->assertContains(
            'app/Support/LegacyWrongExtends.php: legacy support wrapper must extend its imported legacy class directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Support/LegacyBehavior.php: legacy support wrapper must not define behavior; add a dedicated adapter if behavior is needed.',
            $errors->all()
        );
        $this->assertContains(
            'app/Support/LegacyMissingSource.php: imported legacy class file src/User/MissingSource.php does not exist.',
            $errors->all()
        );
        $this->assertCount(4, $errors);
    }

    public function test_legacy_support_wrapper_inventory_errors_report_unlisted_wrappers(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySupportWrapperInventoryErrorsFor',
            [[
                'app/Support/LegacyReporter.php',
                'app/Support/LegacyMissingBoundary.php',
            ]]
        );

        $this->assertContains(
            'app/Support/LegacyMissingBoundary.php: legacy support wrapper is not listed in a specific audit allow-list.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_support_direct_reference_inventory_errors_report_unlisted_support_files(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySupportDirectReferenceInventoryErrorsFor',
            [[
                'app/Support/CurrentUserSession.php' => 'use LeadMax\\TrackYourStats\\System\\Session;',
                'app/Support/UnlistedLegacyAdapter.php' => 'use LeadMax\\TrackYourStats\\User\\User;',
                'app/Support/PlainAdapter.php' => 'use App\\User;',
            ]]
        );

        $this->assertContains(
            'app/Support/UnlistedLegacyAdapter.php: support file references legacy classes but is not listed in a specific audit allow-list.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_boundary_allowed_path_inventory_errors_report_stale_paths(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'boundaryAllowedPathInventoryErrorsFor',
            [[
                'app/Support/CurrentUserSession.php',
                'app/Support/MissingBoundary.php',
            ], [
                'app/Support',
                'app/MissingBoundaryDirectory',
            ]]
        );

        $this->assertContains(
            'app/Support/MissingBoundary.php: audited legacy boundary allow-list file does not exist.',
            $errors->all()
        );
        $this->assertContains(
            'app/MissingBoundaryDirectory: audited legacy boundary allow-list directory does not exist.',
            $errors->all()
        );
        $this->assertCount(2, $errors);
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
            'missing_exception.php: legacy POST PHP route is missing from VerifyCsrfToken exceptions.',
            $errors->all()
        );
        $this->assertContains(
            'stale_exception.php: VerifyCsrfToken exception does not match a registered legacy POST PHP route.',
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

    public function test_native_session_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'nativeSessionDependencyErrorsFor',
            [[
                'app/Company.php' => 'return $_SESSION["COMPANY_SUBDOMAIN"] ?? null;',
                'app/Http/Controllers/BadController.php' => 'if (isset($_GET["adminLogin"])) {}',
                'app/Http/Controllers/BadSignupController.php' => '$_POST = array_merge($_POST, $request->all());',
                'app/Http/Controllers/BadReportController.php' => 'return $_COOKIE["timezone"];',
                'app/Http/Controllers/BadIndexController.php' => 'return $_SERVER["REMOTE_ADDR"];',
                'app/Support/NativeSession.php' => 'return $_SESSION[$key] ?? $default;',
                'app/Support/NativeRequest.php' => '$_POST = array_merge($_POST, $data);',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\NativeSession;',
            ]]
        );

        $this->assertContains(
            'app/Company.php: Use App\\Support\\NativeSession instead of reading or writing the native session superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use Illuminate\\Http\\Request instead of reading query parameters from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Controllers/BadSignupController.php: Use App\\Support\\NativeRequest for explicit legacy POST bridges instead of writing the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Controllers/BadReportController.php: Use Illuminate\\Http\\Request cookie helpers instead of reading cookies from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Controllers/BadIndexController.php: Use Illuminate\\Http\\Request server helpers instead of reading server values from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertCount(5, $errors);
    }

    public function test_legacy_source_native_superglobal_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySourceNativeSuperglobalErrorsFor',
            [[
                'src/BadSessionHelper.php' => 'return $_SESSION["user"];',
                'src/BadQueryHelper.php' => 'return $_GET["id"];',
                'src/BadPostHelper.php' => 'return $_POST["button"];',
                'src/BadCookieHelper.php' => 'return $_COOKIE["timezone"];',
                'src/BadServerHelper.php' => 'return $_SERVER["HTTP_HOST"];',
                'src/CleanHelper.php' => 'return NativeRequest::query("id");',
            ]]
        );

        $this->assertContains(
            'src/BadSessionHelper.php: Use App\\Support\\NativeSession instead of reading or writing the native session superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadQueryHelper.php: Use Illuminate\\Http\\Request instead of reading query parameters from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadPostHelper.php: Use App\\Support\\NativeRequest for explicit legacy POST bridges instead of writing the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadCookieHelper.php: Use Illuminate\\Http\\Request cookie helpers instead of reading cookies from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadServerHelper.php: Use Illuminate\\Http\\Request server helpers instead of reading server values from the native request superglobal directly.',
            $errors->all()
        );
        $this->assertCount(5, $errors);
    }

    public function test_runtime_env_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'runtimeEnvDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => "return env('DB_DATABASE');",
                'resources/views/bad.blade.php' => "{{ env('APP_ENV') }}",
                'routes/bad.php' => "Route::get('/bad', fn () => env('APP_DEBUG'));",
                'src/BadLegacyHelper.php' => "return env('SALE_LOG_DIRECTORY');",
                'app/Http/Controllers/CleanController.php' => "return config('database.connections.mysql.database');",
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use Laravel config values instead of reading environment variables directly at runtime.',
            $errors->all()
        );
        $this->assertContains(
            'resources/views/bad.blade.php: Use Laravel config values instead of reading environment variables directly at runtime.',
            $errors->all()
        );
        $this->assertContains(
            'routes/bad.php: Use Laravel config values instead of reading environment variables directly at runtime.',
            $errors->all()
        );
        $this->assertContains(
            'src/BadLegacyHelper.php: Use Laravel config values instead of reading environment variables directly at runtime.',
            $errors->all()
        );
        $this->assertCount(4, $errors);
    }

    public function test_legacy_source_classes_do_not_read_native_superglobals_directly(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod($command, 'legacySourceNativeSuperglobalErrors', []);

        $this->assertSame([], $errors->all());
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

    public function test_legacy_boundary_dependency_errors_report_forbidden_sources_outside_support(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyBoundaryDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\User;',
                'config/bad.php' => 'LeadMax\\TrackYourStats\\System\\Connection::class;',
                'database/seeds/BadSeeder.php' => 'LeadMax\\TrackYourStats\\Offer\\Offer::VISIBILITY_PRIVATE;',
                'public/bad-entrypoint.php' => 'LeadMax\\TrackYourStats\\System\\Company::loadFromSession();',
                'bootstrap/legacy_loader.php' => 'LeadMax\\TrackYourStats\\System\\Company::loadFromSession();',
                'app/Support/LegacyUser.php' => 'use LeadMax\\TrackYourStats\\User\\User;',
                'resources/views/clean.blade.php' => 'App\\Support\\LegacyUser::selectAllOwnedAffiliates();',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.',
            $errors->all()
        );
        $this->assertContains(
            'config/bad.php: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.',
            $errors->all()
        );
        $this->assertContains(
            'database/seeds/BadSeeder.php: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.',
            $errors->all()
        );
        $this->assertContains(
            'public/bad-entrypoint.php: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.',
            $errors->all()
        );
        $this->assertContains(
            'bootstrap/legacy_loader.php: use an App\\Support wrapper instead of referencing LeadMax\\TrackYourStats directly.',
            $errors->all()
        );
        $this->assertCount(5, $errors);
    }

    public function test_legacy_permissions_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPermissionsDependencyErrorsFor',
            [[
                'routes/web.php' => 'use LeadMax\\TrackYourStats\\User\\Permissions;',
                'app/Http/Traits/BadTrait.php' => 'Permissions::loadFromSession();',
                'src/Offer/BadCreate.php' => 'Permissions::loadFromSession();',
                'app/Support/LegacyPermissions.php' => 'use LeadMax\\TrackYourStats\\User\\Permissions;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyPermissions as Permissions;',
            ]]
        );

        $this->assertContains(
            'routes/web.php: Use App\\Support\\LegacyPermissions instead of importing the legacy permissions class directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Traits/BadTrait.php: Use App\\Support\\CurrentUserSession::permissions() instead of loading permissions from the legacy session directly.',
            $errors->all()
        );
        $this->assertContains(
            'src/Offer/BadCreate.php: Use App\\Support\\CurrentUserSession::permissions() instead of loading permissions from the legacy session directly.',
            $errors->all()
        );
        $this->assertCount(3, $errors);
    }

    public function test_legacy_click_geo_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyClickGeoDependencyErrorsFor',
            [[
                'app/Services/BadService.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickGeo;',
                'app/Support/LegacyClickGeo.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickGeo;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyClickGeo as ClickGeo;',
            ]]
        );

        $this->assertContains(
            'app/Services/BadService.php: Use App\\Support\\LegacyClickGeo instead of importing the legacy ClickGeo class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_click_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyClickDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\Click;',
                'app/Support/LegacyClick.php' => 'use LeadMax\\TrackYourStats\\Clicks\\Click;',
                'app/Support/LegacyClickGeo.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickGeo;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyClick as Click;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyClick instead of importing the legacy click class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_click_vars_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyClickVarsDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickVars;',
                'app/Support/LegacyClickVars.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickVars;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyClickVars as ClickVars;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyClickVars instead of importing the legacy click vars class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_click_searcher_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyClickSearcherDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickSearcher;',
                'app/Support/LegacyClickSearcher.php' => 'use LeadMax\\TrackYourStats\\Clicks\\ClickSearcher;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyClickSearcher as ClickSearcher;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyClickSearcher instead of importing the legacy click searcher class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_conversion_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyConversionDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\Conversion;',
                'app/Support/LegacyConversion.php' => 'use LeadMax\\TrackYourStats\\Clicks\\Conversion;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyConversion as Conversion;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyConversion instead of importing the legacy conversion class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_pending_conversion_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPendingConversionDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\PendingConversion;',
                'app/Support/LegacyPendingConversion.php' => 'use LeadMax\\TrackYourStats\\Clicks\\PendingConversion;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyPendingConversion as PendingConversion;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyPendingConversion instead of importing the legacy pending conversion class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_postback_url_event_handler_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPostBackUrlEventHandlerDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler;',
                'app/Support/LegacyPostBackURLEventHandler.php' => 'use LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyPostBackURLEventHandler as PostBackURLEventHandler;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyPostBackURLEventHandler instead of importing the legacy postback URL event handler directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_click_registration_event_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyClickRegistrationEventDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\URLEvents\\ClickRegistrationEvent;',
                'app/Support/LegacyClickRegistrationEvent.php' => 'use LeadMax\\TrackYourStats\\Clicks\\URLEvents\\ClickRegistrationEvent;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyClickRegistrationEvent as ClickRegistrationEvent;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyClickRegistrationEvent instead of importing the legacy click registration event directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_uid_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyUidDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\UID;',
                'app/Support/LegacyUid.php' => 'use LeadMax\\TrackYourStats\\Clicks\\UID;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyUid as UID;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyUid instead of importing the legacy UID class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_tracking_parameters_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyTrackingParametersDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Clicks\\TrackingParameters;',
                'app/Support/LegacyTrackingParameters.php' => 'use LeadMax\\TrackYourStats\\Clicks\\TrackingParameters;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyTrackingParameters as TrackingParameters;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyTrackingParameters instead of importing the legacy tracking parameters class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_lander_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyLanderDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\Lander;',
                'app/Support/LegacyLander.php' => 'use LeadMax\\TrackYourStats\\System\\Lander;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyLander as Lander;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyLander instead of importing the legacy lander class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_ip_blacklist_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyIpBlackListDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\IPBlackList;',
                'app/Support/LegacyIPBlackList.php' => 'use LeadMax\\TrackYourStats\\System\\IPBlackList;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyIPBlackList as IPBlackList;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyIPBlackList instead of importing the legacy IP blacklist class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_navbar_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyNavBarDependencyErrorsFor',
            [[
                'app/Providers/BadProvider.php' => 'use LeadMax\\TrackYourStats\\System\\NavBar;',
                'app/Support/LegacyNavBar.php' => 'use LeadMax\\TrackYourStats\\System\\NavBar;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyNavBar as NavBar;',
            ]]
        );

        $this->assertContains(
            'app/Providers/BadProvider.php: Use App\\Support\\LegacyNavBar instead of importing the legacy navigation class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_images_uploader_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyImagesUploaderDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader;',
                'app/Support/LegacyImagesUploader.php' => 'use LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyImagesUploader as ImagesUploader;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyImagesUploader instead of importing the legacy image uploader class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_notifications_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyNotificationsDependencyErrorsFor',
            [[
                'app/Providers/BadProvider.php' => 'use LeadMax\\TrackYourStats\\System\\Notifications;',
                'app/Support/LegacyNotifications.php' => 'use LeadMax\\TrackYourStats\\System\\Notifications;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyNotifications as Notifications;',
            ]]
        );

        $this->assertContains(
            'app/Providers/BadProvider.php: Use App\\Support\\LegacyNotifications instead of importing the legacy notifications class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_offer_domain_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyOfferDomainDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Offer\\RepHasOffer;',
                'app/Support/LegacyRepHasOffer.php' => 'use LeadMax\\TrackYourStats\\Offer\\RepHasOffer;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyRepHasOffer as RepHasOffer;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyRepHasOffer instead of importing the legacy offer-assignment class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_offer_support_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyOfferSupportDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Offer\\Campaigns;',
                'app/Support/LegacyCampaigns.php' => 'use LeadMax\\TrackYourStats\\Offer\\Campaigns;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyCampaigns as Campaigns;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyCampaigns instead of importing the legacy campaigns class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_offer_rules_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyOfferRulesDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'new \\LeadMax\\TrackYourStats\\Offer\\Rules\\Handlers\\Geo($data);',
                'app/Support/LegacyGeoRuleHandler.php' => 'use LeadMax\\TrackYourStats\\Offer\\Rules\\Handlers\\Geo;',
                'app/Http/Controllers/CleanController.php' => 'new \\App\\Support\\LegacyGeoRuleHandler($data);',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support legacy offer-rule wrappers instead of referencing legacy offer-rule classes directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_payouts_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPayoutsDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Offer\\Payouts;',
                'app/Support/LegacyPayouts.php' => 'use LeadMax\\TrackYourStats\\Offer\\Payouts;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyPayouts as Payouts;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyPayouts instead of importing the legacy payouts class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_adjustments_log_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyAdjustmentsLogDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog;',
                'app/Support/LegacyAdjustmentsLog.php' => 'use LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyAdjustmentsLog as AdjustmentsLog;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyAdjustmentsLog instead of importing the legacy adjustments log class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_sale_log_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacySaleLogDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Offer\\SaleLog;',
                'app/Support/LegacySaleLog.php' => 'use LeadMax\\TrackYourStats\\Offer\\SaleLog;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacySaleLog as SaleLog;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacySaleLog instead of importing the legacy sale log class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_date_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyDateDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Table\\Date;',
                'resources/views/report/bad.blade.php' => 'new LeadMax\\TrackYourStats\\Table\\Date;',
                'src/Table/Date.php' => 'return $_COOKIE["timezone"];',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\DateHelper as Date;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: The legacy date class is retired; use App\\Support\\DateHelper.',
            $errors->all()
        );
        $this->assertContains(
            'resources/views/report/bad.blade.php: The legacy date class is retired; use App\\Support\\DateHelper.',
            $errors->all()
        );
        $this->assertContains(
            'src/Table/Date.php: Use App\\Support\\NativeRequest::cookie() instead of reading the timezone cookie directly.',
            $errors->all()
        );
        $this->assertCount(3, $errors);
    }

    public function test_legacy_paginate_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyPaginateDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Table\\Paginate;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\PaginationHelper as Paginate;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: The legacy paginate class is retired; use App\\Support\\PaginationHelper.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_assignments_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyAssignmentsDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Table\\Assignments;',
                'app/Support/LegacyAssignments.php' => 'use LeadMax\\TrackYourStats\\Table\\Assignments;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyAssignments as Assignments;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyAssignments instead of importing the legacy assignments class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_tree_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyTreeDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\Tree;',
                'app/Support/LegacyTree.php' => 'use LeadMax\\TrackYourStats\\User\\Tree;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyTree as Tree;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyTree instead of importing the legacy tree class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_user_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyUserDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\User;',
                'app/Support/LegacyUser.php' => 'use LeadMax\\TrackYourStats\\User\\User;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyUser;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyUser instead of importing the legacy user class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_login_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyLoginDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\Login;',
                'app/Support/LegacyLogin.php' => 'use LeadMax\\TrackYourStats\\User\\Login;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyLogin as Login;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyLogin instead of importing the legacy login class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_affiliate_signup_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyAffiliateSignUpDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\AffiliateSignUp;',
                'app/Support/LegacyAffiliateSignUp.php' => 'use LeadMax\\TrackYourStats\\User\\AffiliateSignUp;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyAffiliateSignUp as AffiliateSignUp;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyAffiliateSignUp instead of importing the legacy affiliate signup class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_user_domain_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyUserDomainDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\User\\Bonus;',
                'app/Support/LegacyBonus.php' => 'use LeadMax\\TrackYourStats\\User\\Bonus;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyBonus;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyBonus instead of importing the legacy bonus class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_offer_postback_url_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyOfferPostBackUrlDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'new \\LeadMax\\TrackYourStats\\User\\PostBackURLs\\ConversionPostBackURL($userId, $offerId);',
                'app/Support/LegacyConversionPostBackURL.php' => 'use LeadMax\\TrackYourStats\\User\\PostBackURLs\\ConversionPostBackURL;',
                'app/Http/Controllers/CleanController.php' => 'new \\App\\Support\\LegacyConversionPostBackURL($userId, $offerId);',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyConversionPostBackURL instead of referencing the legacy conversion postback URL class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_admin_login_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyAdminLoginDependencyErrorsFor',
            [[
                'resources/views/layouts/bad.blade.php' => 'new \\LeadMax\\TrackYourStats\\User\\AdminLogin();',
                'resources/views/layouts/clean.blade.php' => 'App\\Support\\RequestContext::hasQuery(\'adminLogin\');',
            ]]
        );

        $this->assertContains(
            'resources/views/layouts/bad.blade.php: The legacy admin-login class is retired; use Laravel request and middleware boundaries.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_notify_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyNotifyDependencyErrorsFor',
            [[
                'resources/views/layouts/bad.blade.php' => '\\LeadMax\\TrackYourStats\\System\\Notify::info($message, \'\');',
                'resources/views/layouts/clean.blade.php' => '{{ session(\'status\') }}',
            ]]
        );

        $this->assertContains(
            'resources/views/layouts/bad.blade.php: The legacy notify class is retired; render notifications through Laravel views.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_company_updater_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyCompanyUpdaterDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Database\\CompanyUpdater;',
                'app/Support/LegacyCompanyUpdater.php' => 'use LeadMax\\TrackYourStats\\Database\\CompanyUpdater;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyCompanyUpdater as CompanyUpdater;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyCompanyUpdater instead of importing the legacy company updater class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_connection_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyConnectionDependencyErrorsFor',
            [[
                'src/User/BadSource.php' => 'use LeadMax\\TrackYourStats\\System\\Connection;',
                'app/Support/LegacyConnection.php' => 'use LeadMax\\TrackYourStats\\System\\Connection;',
                'src/User/CleanSource.php' => 'use App\\Support\\LegacyConnection as Connection;',
            ]]
        );

        $this->assertContains(
            'src/User/BadSource.php: Use App\\Support\\LegacyConnection instead of importing the legacy connection class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_report_html_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyReportHtmlDependencyErrorsFor',
            [[
                'resources/views/report/bad.blade.php' => 'new \\LeadMax\\TrackYourStats\\Report\\Formats\\HTML();',
                'app/Support/LegacyReportHtml.php' => 'use LeadMax\\TrackYourStats\\Report\\Formats\\HTML;',
                'resources/views/report/clean.blade.php' => 'new \\App\\Support\\LegacyReportHtml();',
            ]]
        );

        $this->assertContains(
            'resources/views/report/bad.blade.php: Use App\\Support\\LegacyReportHtml instead of importing the legacy report HTML formatter directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_reporter_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyReporterDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\Reporter;',
                'app/Support/LegacyReporter.php' => 'use LeadMax\\TrackYourStats\\Report\\Reporter;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyReporter as Reporter;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyReporter instead of importing the legacy reporter class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_report_filters_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyReportFiltersDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\Filters\\DollarSign;',
                'app/Support/LegacyDollarSignFilter.php' => 'use LeadMax\\TrackYourStats\\Report\\Filters\\DollarSign;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyDollarSignFilter as DollarSign;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support legacy report filter wrappers instead of importing legacy report filters directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_report_objects_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyReportObjectsDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\AffiliatePayout;',
                'app/Support/LegacyAffiliatePayoutReport.php' => 'use LeadMax\\TrackYourStats\\Report\\AffiliatePayout;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyAffiliatePayoutReport as AffiliatePayout;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyAffiliatePayoutReport instead of importing the legacy affiliate payout report directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_database_connection_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyDatabaseConnectionDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => '\\LeadMax\\TrackYourStats\\Database\\DatabaseConnection::getInstance();',
                'app/Support/LegacyDatabaseConnection.php' => 'use LeadMax\\TrackYourStats\\Database\\DatabaseConnection;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyDatabaseConnection as DatabaseConnection;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyDatabaseConnection instead of referencing the legacy database connection class directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_malformed_legacy_namespace_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'malformedLegacyNamespaceErrorsFor',
            [[
                'src/BadHelper.php' => '\\LeadMax\\TrackYourStats\\LeadMax\\TrackYourStats\\System\\Log($e, null);',
                'src/CleanHelper.php' => '\\Log($e, null);',
            ]]
        );

        $this->assertContains(
            'src/BadHelper.php: Remove the duplicated legacy namespace segment.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_offer_report_repositories_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyOfferReportRepositoriesDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\GodOfferRepository;',
                'app/Support/LegacyGodOfferRepository.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\Offer\\GodOfferRepository;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyGodOfferRepository as GodOfferRepository;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyGodOfferRepository instead of importing the legacy god offer repository directly.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_legacy_employee_report_repositories_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyEmployeeReportRepositoriesDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\AdminEmployeeRepository;',
                'app/Http/Controllers/BadBaseController.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\Repository;',
                'app/Support/LegacyAdminEmployeeRepository.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\AdminEmployeeRepository;',
                'app/Http/Controllers/CleanController.php' => 'use App\\Support\\LegacyAdminEmployeeRepository as AdminEmployeeRepository;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: Use App\\Support\\LegacyAdminEmployeeRepository instead of importing the legacy admin employee repository directly.',
            $errors->all()
        );
        $this->assertContains(
            'app/Http/Controllers/BadBaseController.php: Avoid typehinting the legacy base report repository directly in modern report controllers.',
            $errors->all()
        );
        $this->assertCount(2, $errors);
    }

    public function test_legacy_misc_report_repositories_dependency_errors_report_forbidden_sources(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        $errors = $this->invokeAuditMethod(
            $command,
            'legacyMiscReportRepositoriesDependencyErrorsFor',
            [[
                'app/Http/Controllers/BadController.php' => 'use LeadMax\\TrackYourStats\\Report\\Repositories\\PayoutLogRepository;',
                'app/Http/Controllers/CleanController.php' => 'use App\\PayoutLog;',
            ]]
        );

        $this->assertContains(
            'app/Http/Controllers/BadController.php: The legacy payout-log repository is retired; use the App\\PayoutLog model instead.',
            $errors->all()
        );
        $this->assertCount(1, $errors);
    }

    public function test_audit_hardening_pattern_lists_have_documented_messages(): void
    {
        $command = app(AuditLegacyFallbackCoverage::class);

        foreach ([
            'frontControllerForbiddenPatterns',
            'runtimeBootstrapRequiredPatterns',
            'retiredCompanySessionForbiddenPatterns',
            'retiredCompanySessionAllowedFiles',
            'legacySessionForbiddenPatterns',
            'nativeSessionForbiddenPatterns',
            'nativeSessionAllowedFiles',
            'runtimeEnvForbiddenPatterns',
            'legacyPermissionsForbiddenPatterns',
            'legacyClickGeoForbiddenPatterns',
            'legacyClickForbiddenPatterns',
            'legacyClickVarsForbiddenPatterns',
            'legacyClickSearcherForbiddenPatterns',
            'legacyConversionForbiddenPatterns',
            'legacyPendingConversionForbiddenPatterns',
            'legacyPostBackUrlEventHandlerForbiddenPatterns',
            'legacyClickRegistrationEventForbiddenPatterns',
            'legacyUidForbiddenPatterns',
            'legacyTrackingParametersForbiddenPatterns',
            'legacyLanderForbiddenPatterns',
            'legacyNavBarForbiddenPatterns',
            'legacyIpBlackListForbiddenPatterns',
            'legacyImagesUploaderForbiddenPatterns',
            'legacyNotificationsForbiddenPatterns',
            'legacyOfferDomainForbiddenPatterns',
            'legacyOfferSupportForbiddenPatterns',
            'legacyOfferRulesForbiddenPatterns',
            'legacyPayoutsForbiddenPatterns',
            'legacyAdjustmentsLogForbiddenPatterns',
            'legacySaleLogForbiddenPatterns',
            'legacyDateForbiddenPatterns',
            'legacyPaginateForbiddenPatterns',
            'legacyAssignmentsForbiddenPatterns',
            'legacyTreeForbiddenPatterns',
            'legacyUserForbiddenPatterns',
            'legacyLoginForbiddenPatterns',
            'legacyAffiliateSignUpForbiddenPatterns',
            'legacyUserDomainForbiddenPatterns',
            'legacyOfferPostBackUrlForbiddenPatterns',
            'legacyAdminLoginForbiddenPatterns',
            'legacyNotifyForbiddenPatterns',
            'legacyCompanyUpdaterForbiddenPatterns',
            'legacyConnectionForbiddenPatterns',
            'legacyReportHtmlForbiddenPatterns',
            'legacyReporterForbiddenPatterns',
            'legacyReportFiltersForbiddenPatterns',
            'legacyReportObjectsForbiddenPatterns',
            'legacyDatabaseConnectionForbiddenPatterns',
            'legacyOfferReportRepositoriesForbiddenPatterns',
            'legacyEmployeeReportRepositoriesForbiddenPatterns',
            'legacyMiscReportRepositoriesForbiddenPatterns',
            'legacyMailForbiddenPatterns',
            'legacyBoundaryAllowedDirectories',
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

    public function test_front_controller_has_no_legacy_loader_or_dynamic_legacy_fallback(): void
    {
        $frontController = File::get(public_path('index.php'));

        $this->assertStringNotContainsString('legacy_loader.php', $frontController);
        $this->assertStringNotContainsString('../legacy', $frontController);
        $this->assertStringNotContainsString('legacy/index.php', $frontController);
        $this->assertStringNotContainsString('is_file($file)', $frontController);
        $this->assertStringNotContainsString('include($file)', $frontController);
    }

    public function test_laravel_middleware_owns_runtime_bootstrap(): void
    {
        $kernel = File::get(app_path('Http/Kernel.php'));
        $middleware = File::get(app_path('Http/Middleware/InitializeLegacyRuntime.php'));
        $runtimeBootstrap = File::get(app_path('Support/RuntimeBootstrap.php'));

        $this->assertFileDoesNotExist(base_path('bootstrap/legacy_loader.php'));
        $this->assertStringContainsString('InitializeLegacyRuntime::class', $kernel);
        $this->assertStringContainsString('RuntimeBootstrap::boot()', $middleware);
        $this->assertStringContainsString('private static bool $bootstrapped = false;', $runtimeBootstrap);
        $this->assertStringContainsString('session_status() === PHP_SESSION_NONE', $runtimeBootstrap);
        $this->assertStringContainsString('$connection->setConnection();', $runtimeBootstrap);
        $this->assertStringContainsString('Company::loadFromSession()->setSession();', $runtimeBootstrap);
        $this->assertStringNotContainsString('vendor/autoload.php', $runtimeBootstrap);
        $this->assertStringNotContainsString('Dotenv', $runtimeBootstrap);
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
