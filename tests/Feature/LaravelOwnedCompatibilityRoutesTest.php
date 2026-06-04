<?php

namespace Tests\Feature;

use App\Http\Controllers\AffiliateMassPostbackController;
use App\Http\Controllers\ChatLogController;
use App\Http\Controllers\ClickIdToolController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\DatabaseUpdateController;
use App\Http\Controllers\IPBlacklistController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReportPermissionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

class LaravelOwnedCompatibilityRoutesTest extends TestCase
{
    public function test_public_signup_php_aliases_route_to_laravel_signup_controller(): void
    {
        $this->assertRouteAction('/signup.php', 'GET', SignupController::class . '@show');
        $this->assertRouteAction('/signup.php', 'POST', SignupController::class . '@submit');
        $this->assertRouteAction('/signup_success.php', 'GET', SignupController::class . '@success');
    }

    public function test_admin_legacy_php_urls_route_to_laravel_controllers(): void
    {
        $routes = [
            ['/aff_permissions.php', 'GET', ReportPermissionController::class . '@index'],
            ['/aff_permissions.php', 'POST', ReportPermissionController::class . '@update'],
            ['/add_new_ip_blacklist.php', 'POST', IPBlacklistController::class . '@store'],
            ['/edit_blacklisted_ip.php', 'POST', IPBlacklistController::class . '@updateLegacy'],
            ['/mass_assign_pb.php', 'GET', AffiliateMassPostbackController::class . '@show'],
            ['/mass_assign_pb.php', 'POST', AffiliateMassPostbackController::class . '@update'],
            ['/setup.php', 'GET', CompanySetupController::class . '@create'],
            ['/setup.php', 'POST', CompanySetupController::class . '@store'],
            ['/update_databases.php', 'GET', DatabaseUpdateController::class . '@run'],
            ['/update_databases.php', 'POST', DatabaseUpdateController::class . '@run'],
            ['/scripts/sale_log.php', 'POST', ChatLogController::class . '@legacyDeleteSaleLogImage'],
            ['/upload_logo.php', 'POST', SettingsController::class . '@uploadLogo'],
            ['/upload_favicon.php', 'POST', SettingsController::class . '@uploadFavicon'],
            ['/dontaskdonttell.php', 'GET', ClickIdToolController::class],
        ];

        foreach ($routes as [$uri, $method, $action]) {
            $this->assertRouteAction($uri, $method, $action);
        }
    }

    public function test_modern_setup_and_database_update_routes_stay_registered(): void
    {
        $this->assertRouteAction('/admin/setup', 'GET', CompanySetupController::class . '@create');
        $this->assertRouteAction('/admin/setup', 'POST', CompanySetupController::class . '@store');
        $this->assertRouteAction('/admin/database-updates', 'GET', DatabaseUpdateController::class . '@index');
        $this->assertRouteAction('/admin/database-updates', 'POST', DatabaseUpdateController::class . '@run');
    }

    public function test_settings_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/SettingsController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_offer_controller_does_not_load_legacy_company_for_offer_url_management(): void
    {
        $controller = File::get(app_path('Http/Controllers/OfferController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\URLs', $controller);
    }

    public function test_legacy_offer_domain_helpers_do_not_import_legacy_company_class(): void
    {
        foreach ([
            base_path('src/Offer/View.php'),
            base_path('src/Offer/URLs.php'),
            base_path('src/Offer/Rules/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
        }
    }

    public function test_low_risk_modern_controllers_use_current_session_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/BonusController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            app_path('Http/Controllers/DashboardController.php'),
            app_path('Http/Controllers/EmailPoolController.php'),
            app_path('Http/Controllers/GlobalPostbackController.php'),
            app_path('Http/Controllers/IPBlacklistController.php'),
            app_path('Http/Controllers/NotificationController.php'),
            app_path('Http/Controllers/ReportPermissionController.php'),
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/AggregateReportController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/SalaryController.php'),
            app_path('Http/Controllers/Sms/SmsClientController.php'),
            app_path('Http/Controllers/Sms/SmsController.php'),
            app_path('Http/Controllers/SmsOrderController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }

        $smsApiController = File::get(app_path('Http/Controllers/Sms/SmsApiController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $smsApiController);
    }

    public function test_legacy_post_compatibility_urls_keep_csrf_exceptions(): void
    {
        $middleware = app(VerifyCsrfToken::class);
        $reflection = new ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);
        $legacyPostUris = collect(Route::getRoutes())
            ->filter(fn ($route) => in_array('POST', $route->methods(), true))
            ->map(fn ($route) => trim($route->uri(), '/'))
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->unique()
            ->sort()
            ->values();
        $legacyPhpCsrfExceptions = collect($except)
            ->map(fn ($uri) => trim($uri, '/'))
            ->filter(fn ($uri) => str_contains($uri, '.php'))
            ->unique()
            ->sort()
            ->values();

        $this->assertNotEmpty($legacyPostUris);

        foreach ($legacyPostUris as $legacyPostUri) {
            $this->assertContains($legacyPostUri, $except);
        }

        foreach ($legacyPhpCsrfExceptions as $legacyPhpCsrfException) {
            $this->assertContains($legacyPhpCsrfException, $legacyPostUris);
        }
    }

    private function assertRouteAction(string $uri, string $method, string $expectedAction): void
    {
        $this->assertSame(
            $expectedAction,
            Route::getRoutes()->match(Request::create($uri, $method))->getActionName()
        );
    }
}
