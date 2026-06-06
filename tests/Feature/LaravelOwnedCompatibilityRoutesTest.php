<?php

namespace Tests\Feature;

use App\Http\Controllers\AffiliateMassPostbackController;
use App\Http\Controllers\ChatLogController;
use App\Http\Controllers\ClickIdToolController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\DatabaseUpdateController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\IPBlacklistController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReportPermissionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SignupController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Providers\AppServiceProvider;
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
            app_path('Http/Controllers/OfferController.php'),
            app_path('Http/Controllers/ReportPermissionController.php'),
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/AggregateReportController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/Report/ConversionReportController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            app_path('Http/Controllers/SalaryController.php'),
            app_path('Http/Controllers/Sms/SmsClientController.php'),
            app_path('Http/Controllers/Sms/SmsController.php'),
            app_path('Http/Controllers/SmsOrderController.php'),
            app_path('Http/Controllers/UserController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }

        $smsApiController = File::get(app_path('Http/Controllers/Sms/SmsApiController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $smsApiController);
    }

    public function test_laravel_session_boundaries_use_current_session_boundary(): void
    {
        foreach ([
            app_path('Click.php'),
            app_path('Http/Controllers/LegacyCompatibilityController.php'),
            app_path('Http/Middleware/LegacyAccountTypeMiddleware.php'),
            app_path('Http/Middleware/LegacyPermissionMiddleware.php'),
            app_path('Providers/AppServiceProvider.php'),
            app_path('Services/Repositories/Offer/OfferAffiliateClicksRepository.php'),
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
            app_path('Services/SMS/Text69.php'),
            app_path('User.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_modern_permission_reads_use_legacy_permissions_boundary(): void
    {
        foreach ([
            base_path('routes/web.php'),
            app_path('Http/Controllers/BonusController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
            app_path('Http/Controllers/DashboardController.php'),
            app_path('Http/Controllers/NotificationController.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/UserController.php'),
            app_path('Http/Traits/ClickTraits.php'),
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyPermissions as Permissions', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Permissions', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\Permissions',
            File::get(app_path('Support/LegacyPermissions.php'))
        );
    }

    public function test_modern_mail_reads_use_legacy_mail_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/LegacyCompatibilityController.php'),
            app_path('Http/Controllers/NotificationController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyMail as Mail', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Mail', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Mail',
            File::get(app_path('Support/LegacyMail.php'))
        );
    }

    public function test_modern_click_geo_reads_use_legacy_click_geo_boundary(): void
    {
        foreach ([
            app_path('Console/Commands/BackfillClicksGeoFromIp.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            app_path('Http/Traits/ClickTraits.php'),
            app_path('Services/ClickGeoCacheService.php'),
            app_path('Services/CountryReportBuilderService.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyClickGeo as ClickGeo', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickGeo', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\ClickGeo',
            File::get(app_path('Support/LegacyClickGeo.php'))
        );
    }

    public function test_modern_click_id_reads_use_legacy_uid_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/ClickIdToolController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyUid as UID', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\UID', $contents);
        }

        foreach ([
            app_path('Exceptions/RegistrationEventExceptions/ClickConvertedException.php'),
            app_path('Exceptions/RegistrationEventExceptions/InvalidClickException.php'),
        ] as $path) {
            $this->assertStringNotContainsString(
                'LeadMax\\TrackYourStats\\Clicks\\UID',
                File::get($path)
            );
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\UID',
            File::get(app_path('Support/LegacyUid.php'))
        );
    }

    public function test_modern_lander_reads_use_legacy_lander_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/IndexController.php'),
            app_path('Http/Controllers/LanderController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyLander as Lander', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Lander', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Lander',
            File::get(app_path('Support/LegacyLander.php'))
        );
    }

    public function test_modern_payout_reads_use_legacy_payouts_boundary(): void
    {
        foreach ([
            app_path('Click.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/Report/ConversionReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyPayouts as Payouts', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Payouts', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Payouts',
            File::get(app_path('Support/LegacyPayouts.php'))
        );
    }

    public function test_modern_date_reads_use_legacy_date_boundary(): void
    {
        foreach ([
            app_path('BonusOffer.php'),
            app_path('Console/Commands/AggregateReportData.php'),
            app_path('Console/Commands/PayoutLogsRun.php'),
            app_path('Http/Controllers/Report/ReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDate as Date', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Date', $contents);
        }

        $userController = File::get(app_path('Http/Controllers/UserController.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Date', $userController);

        foreach ([
            resource_path('views/report/clicks/affiliate.blade.php'),
            resource_path('views/report/clicks/offer.blade.php'),
            resource_path('views/report/clicks/subid.blade.php'),
            resource_path('views/report/clicks/subid-in-country.blade.php'),
            resource_path('views/report/conversions/affiliate.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDate', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Date', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Table\\Date',
            File::get(app_path('Support/LegacyDate.php'))
        );
    }

    public function test_modern_paginate_reads_use_legacy_paginate_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/UserController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyPaginate as Paginate', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Paginate', $contents);
        }

        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Paginate', $offerController);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Table\\Paginate',
            File::get(app_path('Support/LegacyPaginate.php'))
        );
    }

    public function test_modern_tracking_parameter_reads_use_legacy_tracking_parameters_boundary(): void
    {
        $controller = File::get((new ReflectionClass(IndexController::class))->getFileName());

        $this->assertStringContainsString('App\\Support\\LegacyTrackingParameters as TrackingParameters', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\TrackingParameters', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\TrackingParameters',
            File::get(app_path('Support/LegacyTrackingParameters.php'))
        );
    }

    public function test_modern_ip_blacklist_reads_use_legacy_ip_blacklist_boundary(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            (new ReflectionClass(IPBlacklistController::class))->getFileName(),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyIPBlackList as IPBlackList', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\IPBlackList', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\IPBlackList',
            File::get(app_path('Support/LegacyIPBlackList.php'))
        );
    }

    public function test_modern_sale_log_image_uploads_use_legacy_images_uploader_boundary(): void
    {
        $controller = File::get((new ReflectionClass(ChatLogController::class))->getFileName());

        $this->assertStringContainsString('App\\Support\\LegacyImagesUploader as ImagesUploader', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader',
            File::get(app_path('Support/LegacyImagesUploader.php'))
        );
    }

    public function test_modern_notification_reads_use_legacy_notifications_boundary(): void
    {
        foreach ([
            (new ReflectionClass(AppServiceProvider::class))->getFileName(),
            (new ReflectionClass(OfferController::class))->getFileName(),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyNotifications as Notifications', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Notifications', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Notifications',
            File::get(app_path('Support/LegacyNotifications.php'))
        );
    }

    public function test_legacy_report_domain_helpers_use_current_session_boundary(): void
    {
        foreach ([
            base_path('src/Report/Affiliate.php'),
            base_path('src/Report/Employee.php'),
            base_path('src/Report/Formats/HTML.php'),
            base_path('src/Report/ID/Clicks.php'),
            base_path('src/Report/Offer.php'),
            base_path('src/Report/Repositories/BannedUsersRepository.php'),
            base_path('src/Report/Repositories/Employee/AdminEmployeeRepository.php'),
            base_path('src/Report/Repositories/Employee/GodEmployeeRepository.php'),
            base_path('src/Report/Repositories/Employee/ManagerEmployeeRepository.php'),
            base_path('src/Report/Repositories/Offer/AdminOfferRepository.php'),
            base_path('src/Report/Repositories/Offer/GodOfferRepository.php'),
            base_path('src/Report/Repositories/Offer/ManagerOfferRepository.php'),
            base_path('src/Report/Repositories/SaleLogRepository.php'),
            base_path('src/Report/Repositories/SubVarRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_legacy_user_offer_click_and_notification_helpers_use_current_session_boundary(): void
    {
        foreach ([
            base_path('src/Clicks/Conversion.php'),
            base_path('src/Offer/Campaigns.php'),
            base_path('src/Offer/Create.php'),
            base_path('src/Offer/Offer.php'),
            base_path('src/Offer/RepHasOffer.php'),
            base_path('src/Offer/SaleLog.php'),
            base_path('src/Offer/Update.php'),
            base_path('src/Offer/View.php'),
            base_path('src/System/Notifications.php'),
            base_path('src/Table/ReportBase.php'),
            base_path('src/User/Bonus.php'),
            base_path('src/User/Create.php'),
            base_path('src/User/Login.php'),
            base_path('src/User/Permissions.php'),
            base_path('src/User/Referrals.php'),
            base_path('src/User/Salary.php'),
            base_path('src/User/Update.php'),
            base_path('src/User/User.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
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
