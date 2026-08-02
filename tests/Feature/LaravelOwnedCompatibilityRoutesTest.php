<?php

namespace Tests\Feature;

use App\Http\Controllers\ChatLogController;
use App\Http\Controllers\ClickIdToolController;
use App\Http\Controllers\CompanySetupController;
use App\Http\Controllers\DatabaseUpdateController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\IPBlacklistController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

class LaravelOwnedCompatibilityRoutesTest extends TestCase
{
    public function test_public_auth_flows_use_legacy_auth_boundaries(): void
    {
        $loginController = File::get(app_path('Http/Controllers/LegacyLoginController.php'));

        $this->assertStringContainsString('App\\Support\\UserDomain\\Login', $loginController);
        $this->assertStringContainsString('App\\Support\\UserDomain\\User', $loginController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Login', $loginController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $loginController);

        $signupController = File::get(app_path('Http/Controllers/SignupController.php'));

        $this->assertStringContainsString('App\\Support\\UserDomain\\AffiliateSignUp', $signupController);
        $this->assertStringContainsString('App\\Support\\UserDomain\\User', $signupController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\AffiliateSignUp', $signupController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $signupController);

        foreach ([
            app_path('Http/Controllers/Auth/ForgotPasswordController.php'),
            app_path('Http/Middleware/LegacyUserAuth.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\UserDomain\\User', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $contents);
        }

        $this->assertFileDoesNotExist(app_path('Support/LegacyLogin.php'));
        $login = File::get(app_path('Support/UserDomain/Login.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $login);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $login);
        $this->assertStringContainsString('App\\Support\\NativeRequest', $login);
        $this->assertStringContainsString('App\\Support\\NativeSession', $login);
        $this->assertStringNotContainsString('$_POST', $login);
        $this->assertStringNotContainsString('$_SERVER', $login);
        $this->assertStringNotContainsString('$_SESSION', $login);
        $this->assertStringContainsString('NativeSession::destroy()', $login);
        $this->assertStringNotContainsString('session_destroy()', $login);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAffiliateSignUp.php'));
        $this->assertStringContainsString(
            'App\\Support\\Connection',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertStringContainsString(
            'App\\Support\\NativeRequest',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertStringContainsString(
            'App\\Support\\DatabaseConnection',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertStringNotContainsString(
            '$_POST',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\System\\Connection',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Database\\DatabaseConnection',
            File::get(app_path('Support/UserDomain/AffiliateSignUp.php'))
        );
        $this->assertFileDoesNotExist(app_path('Support/LegacyUser.php'));
        $this->assertFileDoesNotExist(base_path('src/System/Connection.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyConnection.php'));
    }

    public function test_modern_user_reads_use_legacy_user_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/BonusController.php'),
            app_path('Http/Controllers/OfferController.php'),
            app_path('Http/Controllers/UserController.php'),
            app_path('Support/Conversion.php'),
            app_path('Support/Tracking/Events/UrlEvent.php'),
            app_path('Support/DatabaseUpdates/Versions/V158.php'),
            app_path('Support/OfferDomain/Create.php'),
            app_path('Support/OfferDomain/RepHasOffer.php'),
            app_path('Support/OfferDomain/SaleLog.php'),
            app_path('Support/OfferDomain/Update.php'),
            app_path('Support/OfferDomain/View.php'),
            app_path('Support/UserDomain/PostBackURLs/ConversionPostBackURL.php'),
            app_path('Support/UserDomain/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\UserDomain\\User', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $contents);
        }

        $clickVariables = File::get(app_path('Support/ClickVariables.php'));
        $this->assertStringContainsString('LegacyUser::SelectOne', $clickVariables);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $clickVariables);

        $this->assertFileDoesNotExist(app_path('Support/LegacyUser.php'));
        $this->assertFileDoesNotExist(base_path('src/System/Session.php'));
        $currentUserSession = File::get(app_path('Support/CurrentUserSession.php'));
        $this->assertStringContainsString('NativeSession::', $currentUserSession);
        $this->assertStringContainsString('NativeRequest::', $currentUserSession);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats', $currentUserSession);

        $legacyUser = File::get(app_path('Support/UserDomain/User.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $legacyUser);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $legacyUser);
        $this->assertStringContainsString('App\\Support\\NativeRequest', $legacyUser);
        $this->assertStringNotContainsString('$_COOKIE', $legacyUser);
        $this->assertStringNotContainsString('$_POST', $legacyUser);
    }

    public function test_modern_user_domain_helpers_use_legacy_boundaries(): void
    {
        $bonusController = File::get(app_path('Http/Controllers/BonusController.php'));

        $this->assertStringContainsString('App\\Support\\UserDomain\\Bonus', $bonusController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Bonus', $bonusController);

        foreach ([
            app_path('Support/Conversion.php'),
            app_path('Support/Tracking/Events/BonusRegistrationEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\UserDomain\\Bonus', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Bonus', $contents);
        }

        $salaryController = File::get(app_path('Http/Controllers/SalaryController.php'));

        $this->assertStringContainsString('App\\Support\\UserDomain\\Salary', $salaryController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Salary', $salaryController);

        $globalPostbackController = File::get(app_path('Http/Controllers/GlobalPostbackController.php'));

        $this->assertStringContainsString("DB::table('user_postbacks')", $globalPostbackController);
        $this->assertStringNotContainsString('LegacyPostBackUrl', $globalPostbackController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackUrl', $globalPostbackController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyPostBackUrl.php'));
        $this->assertFileDoesNotExist(base_path('src/User/PostBackUrl.php'));

        $userController = File::get(app_path('Http/Controllers/UserController.php'));

        foreach ([
            'App\\Support\\UserDomain\\Bonus',
            'App\\Support\\UserDomain\\Privileges',
            'App\\Support\\UserDomain\\Referrals',
            'App\\Support\\UserDomain\\ReportPermissions',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $userController);
        }

        foreach ([
            app_path('Support/Conversion.php') => 'App\\Support\\UserDomain\\Referrals',
            app_path('Support/DatabaseUpdates/Versions/V148.php') => 'App\\Support\\UserDomain\\ReportPermissions',
            app_path('Support/OfferDomain/Deduction.php') => 'App\\Support\\UserDomain\\Referrals',
            app_path('Support/OfferDomain/Offer.php') => 'App\\Support\\UserDomain\\Privileges',
        ] as $path => $expectedImport) {
            $contents = File::get($path);

            $this->assertStringContainsString($expectedImport, $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Privileges', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Referrals', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\ReportPermissions', $contents);
        }

        foreach ([
            'LeadMax\\TrackYourStats\\User\\Bonus',
            'LeadMax\\TrackYourStats\\User\\Privileges',
            'LeadMax\\TrackYourStats\\User\\Referrals',
            'LeadMax\\TrackYourStats\\User\\ReportPermissions',
        ] as $forbiddenImport) {
            $this->assertStringNotContainsString($forbiddenImport, $userController);
        }

        foreach ([
            'LegacyBonus.php',
            'LegacySalary.php',
            'LegacyPrivileges.php',
            'LegacyReferrals.php',
            'LegacyReportPermissions.php',
        ] as $wrapper) {
            $this->assertFileDoesNotExist(app_path("Support/{$wrapper}"));
        }

        foreach ([
            app_path('Support/UserDomain/BanUser.php'),
            app_path('Support/UserDomain/Bonus.php'),
            app_path('Support/UserDomain/Create.php'),
            app_path('Support/UserDomain/CreateUser.php'),
            app_path('Support/UserDomain/Permissions.php'),
            app_path('Support/UserDomain/Privileges.php'),
            app_path('Support/UserDomain/Referrals.php'),
            app_path('Support/UserDomain/ReportPermissions.php'),
            app_path('Support/UserDomain/Salary.php'),
            app_path('Support/UserDomain/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }

        foreach ([
            app_path('Support/UserDomain/Permissions.php'),
            app_path('Support/UserDomain/Referrals.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\NativeSession', $contents);
            $this->assertStringNotContainsString('$_SESSION', $contents);
        }

        $create = File::get(app_path('Support/UserDomain/Create.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $create);
        $this->assertStringNotContainsString('$_GET', $create);
        $this->assertStringNotContainsString('$_POST', $create);

        $update = File::get(app_path('Support/UserDomain/Update.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $update);
        $this->assertStringNotContainsString('$_COOKIE', $update);
        $this->assertStringNotContainsString('$_GET', $update);
        $reportPermissions = File::get(app_path('Support/UserDomain/ReportPermissions.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $reportPermissions);
        $this->assertStringNotContainsString('$_POST', $reportPermissions);

        $deduction = File::get(app_path('Support/OfferDomain/Deduction.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $deduction);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $deduction);
    }

    public function test_modern_offer_domain_helpers_use_laravel_owned_classes(): void
    {
        foreach ([
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/OfferController.php'),
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
            app_path('Support/Tracking/Events/ConversionRegistrationEvent.php'),
            app_path('Support/Tracking/Events/UrlEvent.php'),
            app_path('Support/DatabaseUpdates/Versions/V158.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\OfferDomain\\Offer', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Offer', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\RepHasOffer', $contents);
        }

        foreach ([
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/OfferController.php'),
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
            app_path('Support/DatabaseUpdates/Versions/V158.php'),
            app_path('Support/UserDomain/Create.php'),
            app_path('Support/UserDomain/CreateUser.php'),
            app_path('Support/UserDomain/PostBackURLs/ConversionPostBackURL.php'),
            app_path('Support/UserDomain/User.php'),
        ] as $path) {
            $this->assertStringContainsString('App\\Support\\OfferDomain\\RepHasOffer', File::get($path));
        }

        $clickVariables = File::get(app_path('Support/ClickVariables.php'));
        $this->assertStringContainsString('OfferSupport::selectOneQuery', $clickVariables);
        $this->assertStringContainsString('RepHasOffer::getPostbackURL', $clickVariables);

        $userController = File::get(app_path('Http/Controllers/UserController.php'));

        $this->assertStringContainsString('App\\Support\\OfferDomain\\RepHasOffer', $userController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\RepHasOffer', $userController);

        $offerCreateView = File::get(resource_path('views/offer/create.blade.php'));

        $this->assertStringContainsString('App\\Support\\OfferDomain\\Offer', $offerCreateView);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Offer', $offerCreateView);

        $this->assertFileDoesNotExist(app_path('Support/LegacyOffer.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyRepHasOffer.php'));
        $this->assertFileDoesNotExist(base_path('src/Offer/Offer.php'));
        $this->assertFileDoesNotExist(base_path('src/Offer/RepHasOffer.php'));

        $offer = File::get(app_path('Support/OfferDomain/Offer.php'));
        $repHasOffer = File::get(app_path('Support/OfferDomain/RepHasOffer.php'));
        $offerUpdate = File::get(app_path('Support/OfferDomain/Update.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $offer);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $offer);
        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $repHasOffer);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $repHasOffer);
        $this->assertStringContainsString('App\\Support\\NativeRequest', $repHasOffer);
        $this->assertStringNotContainsString('$_POST', $repHasOffer);
        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $offerUpdate);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $offerUpdate);
        $this->assertStringContainsString('App\\Support\\NativeRequest', $offerUpdate);
        $this->assertStringNotContainsString('$_POST', $offerUpdate);
    }

    public function test_modern_offer_postback_urls_use_legacy_boundaries(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));

        foreach ([
            'App\\Support\\UserDomain\\PostBackURLs\\ConversionPostBackURL',
            'App\\Support\\UserDomain\\PostBackURLs\\FreePostBackURL',
            'App\\Support\\UserDomain\\PostBackURLs\\DeductionPostBackURL',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $offerController);
        }

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackURLs', $offerController);

        foreach ([
            app_path('Support/Tracking/Events/ConversionRegistrationEvent.php') => 'App\\Support\\UserDomain\\PostBackURLs\\ConversionPostBackURL',
            app_path('Support/Tracking/Events/DeductionRegistrationEvent.php') => 'App\\Support\\UserDomain\\PostBackURLs\\DeductionPostBackURL',
            app_path('Support/Tracking/Events/FreeSignUpRegistrationEvent.php') => 'App\\Support\\UserDomain\\PostBackURLs\\FreePostBackURL',
        ] as $path => $expectedImport) {
            $contents = File::get($path);

            $this->assertStringContainsString($expectedImport, $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackURLs', $contents);
        }

        foreach ([
            'LegacyConversionPostBackURL.php',
            'LegacyFreePostBackURL.php',
            'LegacyDeductionPostBackURL.php',
        ] as $wrapper) {
            $this->assertFileDoesNotExist(app_path("Support/{$wrapper}"));
        }

        foreach ([
            app_path('Support/UserDomain/PostBackURLs/ConversionPostBackURL.php'),
            app_path('Support/UserDomain/PostBackURLs/FreePostBackURL.php'),
            app_path('Support/UserDomain/PostBackURLs/DeductionPostBackURL.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_modern_offer_support_helpers_use_laravel_owned_classes(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $legacySeedVersion = File::get(app_path('Support/DatabaseUpdates/Versions/V158.php'));

        $this->assertStringContainsString('App\\Support\\OfferDomain\\Campaigns', $offerController);
        $this->assertStringContainsString('App\\Support\\OfferDomain\\Campaigns', $legacySeedVersion);
        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $legacySeedVersion);
        $this->assertStringContainsString(
            'App\\Support\\OfferDomain\\Caps',
            File::get(app_path('Support/OfferDomain/Rules/Device.php'))
        );
        $this->assertStringContainsString('App\\Support\\OfferDomain\\CreateOffer', $legacySeedVersion);
        $this->assertStringContainsString(
            'App\\Support\\OfferDomain\\FreeSignUp',
            File::get(app_path('Support/Tracking/Events/FreeSignUpRegistrationEvent.php'))
        );
        $this->assertStringContainsString('App\\Support\\OfferDomain\\View as OfferView', $offerController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Campaigns', $offerController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Campaigns', $legacySeedVersion);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $legacySeedVersion);
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Caps',
            File::get(app_path('Support/OfferDomain/Rules/Device.php'))
        );
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\CreateOffer', $legacySeedVersion);
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Offer\\FreeSignUp',
            File::get(app_path('Support/Tracking/Events/FreeSignUpRegistrationEvent.php'))
        );
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\View', $offerController);

        $this->assertFileDoesNotExist(app_path('Support/LegacyCaps.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyCampaigns.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyCreateOffer.php'));
        $this->assertStringContainsString(
            'App\\Support\\DatabaseConnection',
            File::get(app_path('Support/OfferDomain/CreateOffer.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Database\\DatabaseConnection',
            File::get(app_path('Support/OfferDomain/CreateOffer.php'))
        );
        $this->assertFileDoesNotExist(app_path('Support/LegacyFreeSignUp.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyOfferView.php'));

        $freeSignUp = File::get(app_path('Support/OfferDomain/FreeSignUp.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $freeSignUp);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $freeSignUp);

        foreach ([
            app_path('Support/OfferDomain/Caps.php'),
            app_path('Support/OfferDomain/Campaigns.php'),
            app_path('Support/OfferDomain/View.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }

        $offerView = File::get(app_path('Support/OfferDomain/View.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $offerView);
        $this->assertStringNotContainsString('$_GET', $offerView);
        $this->assertStringNotContainsString('$_SERVER', $offerView);
    }

    public function test_modern_offer_rule_helpers_use_legacy_boundaries(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $clickRegistrationEvent = File::get(app_path('Support/Tracking/Events/ClickRegistrationEvent.php'));

        foreach ([
            'App\\Support\\OfferDomain\\Rules\\Handlers\\Device as DeviceRuleHandler',
            'App\\Support\\OfferDomain\\Rules\\Handlers\\Geo as GeoRuleHandler',
            'App\\Support\\OfferDomain\\Rules\\Handlers\\NoneUnique as NoneUniqueRuleHandler',
            'App\\Support\\OfferDomain\\Rules as OfferRules',
            'App\\Support\\OfferDomain\\Rules\\Geo as GeoRule',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $offerController);
        }

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Rules', $offerController);
        $this->assertStringContainsString('App\\Support\\OfferDomain\\Rules', $clickRegistrationEvent);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Rules', $clickRegistrationEvent);

        foreach (['LegacyOfferRules.php', 'LegacyOfferRuleGeo.php', 'LegacyGeoRuleHandler.php', 'LegacyDeviceRuleHandler.php', 'LegacyNoneUniqueRuleHandler.php'] as $wrapper) {
            $this->assertFileDoesNotExist(app_path("Support/{$wrapper}"));
        }

        foreach ([
            app_path('Support/OfferDomain/Rules.php'),
            app_path('Support/OfferDomain/Rules/Handlers/Device.php'),
            app_path('Support/OfferDomain/Rules/Handlers/Geo.php'),
            app_path('Support/OfferDomain/Rules/Handlers/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }

        $geoRule = File::get(app_path('Support/OfferDomain/Rules/Geo.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $geoRule);
        $this->assertStringContainsString('NativeRequest::clientIp()', $geoRule);
        $this->assertStringContainsString('App\\Services\\GeoIpDatabase', $geoRule);
        $this->assertStringNotContainsString("config('services.geo.ip_database')", $geoRule);
        $this->assertStringNotContainsString('GEO_IP_DATABASE', $geoRule);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_CLIENT_IP')", $geoRule);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_X_FORWARDED_FOR')", $geoRule);
        $this->assertStringNotContainsString('$_SERVER', $geoRule);
    }

    public function test_admin_legacy_php_urls_route_to_laravel_controllers(): void
    {
        $routes = [
            ['/click-id-tool', 'GET', ClickIdToolController::class],
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

    public function test_modern_database_updates_use_legacy_company_updater_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/DatabaseUpdateController.php'));
        $companyUpdater = File::get(app_path('Support/DatabaseUpdates/CompanyUpdater.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseUpdates\\CompanyUpdater', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\CompanyUpdater', $controller);
        $this->assertStringContainsString('App\\Support\\Connection', $companyUpdater);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Connection', $companyUpdater);

        $this->assertFileDoesNotExist(base_path('src/Database/CompanyUpdater.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyCompanyUpdater.php'));
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
            app_path('Support/OfferDomain/View.php'),
            app_path('Support/OfferDomain/URLs.php'),
            app_path('Support/OfferDomain/Rules/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
        }

        $urls = File::get(app_path('Support/OfferDomain/URLs.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $urls);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $urls);
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
            app_path('Http/Traits/ClickTraits.php'),
            app_path('Http/Controllers/UserController.php'),
        ] as $path) {
            $contents = File::get($path);

            if ($path === app_path('Support/Conversion.php')) {
                $this->assertStringContainsString('CurrentUserSession::', $contents);
            } else {
                $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            }
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }

        $smsApiController = File::get(app_path('Http/Controllers/Sms/SmsApiController.php'));
        $smsClientController = File::get(app_path('Http/Controllers/Sms/SmsClientController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $smsApiController);
        $this->assertStringContainsString('App\\Services\\SmsApiEndpoint', $smsClientController);
        $this->assertStringNotContainsString("config('services.sms.base_url')", $smsClientController);
        $this->assertStringNotContainsString('SMS_URL', $smsClientController);
    }

    public function test_laravel_session_boundaries_use_current_session_boundary(): void
    {
        foreach ([
            app_path('Click.php'),
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

        $text69 = File::get(app_path('Services/SMS/Text69.php'));

        $this->assertStringContainsString('App\\Services\\SmsApiEndpoint', $text69);
        $this->assertStringNotContainsString("config('services.sms.base_url')", $text69);
        $this->assertStringNotContainsString('SMS_URL', $text69);
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
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\UserDomain\\Permissions', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Permissions', $contents);
        }

        $this->assertFileDoesNotExist(app_path('Support/LegacyPermissions.php'));
    }

    public function test_modern_mail_reads_use_legacy_mail_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/Auth/ForgotPasswordController.php'),
            app_path('Http/Controllers/NotificationController.php'),
            app_path('Support/UserDomain/User.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Mail', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Mail', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/System/Mail.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyMail.php'));
        $this->assertFileDoesNotExist(base_path('src/User/PasswordReset.php'));
        $this->assertFileDoesNotExist(app_path('Support/UserDomain/PasswordReset.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats', File::get(app_path('Support/Mail.php')));
    }

    public function test_runtime_click_geo_reads_use_laravel_support(): void
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

            $this->assertStringContainsString('App\\Support\\ClickGeo', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickGeo', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyClickGeo', $contents);
        }

        $this->assertFileExists(app_path('Support/ClickGeo.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyClickGeo.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/ClickGeo.php'));
    }

    public function test_runtime_click_writes_use_laravel_support(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Click', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\Click;', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyClick', $contents);
        }

        $this->assertFileExists(app_path('Support/Click.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyClick.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/Click.php'));

        $click = File::get(app_path('Support/Click.php'));
        $this->assertStringContainsString('DatabaseConnection::getInstance()', $click);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $click);
        $this->assertStringContainsString('NativeRequest::', $click);
        $this->assertStringContainsString('App\\Services\\GeoIpDatabase', $click);
        $this->assertStringNotContainsString("config('services.geo.ip_database')", $click);
        $this->assertStringNotContainsString('GEO_IP_DATABASE', $click);
        $this->assertStringNotContainsString('$_GET', $click);
        $this->assertStringNotContainsString('$_SERVER', $click);

        $cookie = File::get(app_path('Support/Tracking/ClickCookie.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $cookie);
        $this->assertStringNotContainsString('$_COOKIE', $cookie);
        $this->assertFileDoesNotExist(base_path('src/Clicks/Cookie.php'));

        foreach ([
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
            app_path('Support/OfferDomain/Rules/NoneUnique.php'),
        ] as $path) {
            $this->assertStringContainsString('App\\Support\\Tracking\\ClickCookie', File::get($path));
        }

        $clickCookie = (new ReflectionClass(\App\Support\Tracking\ClickCookie::class))->newInstanceWithoutConstructor();
        $clickCookie->affid = 12;
        $clickCookie->offid = 34;
        $clickCookie->cookie = [];
        $clickCookie->transferCookieAlreadySet = false;
        $this->assertTrue($clickCookie->isUnique());
        $clickCookie->registerClick();
        $this->assertFalse($clickCookie->isUnique());
        $this->assertSame([12 => [34]], $clickCookie->cookie);
    }

    public function test_runtime_click_variables_use_laravel_boundary(): void
    {
        foreach ([
            app_path('Support/Tracking/Events/UrlEvent.php'),
            app_path('Support/DatabaseUpdates/Versions/V130.php'),
            app_path('Support/DatabaseUpdates/Versions/V164.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\ClickVariables as ClickVars', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickVars', $contents);
        }

        $clickVars = File::get(app_path('Support/ClickVariables.php'));
        $this->assertStringContainsString('DatabaseConnection::getInstance()', $clickVars);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $clickVars);
        $this->assertStringContainsString('NativeRequest::queryAll()', $clickVars);
        $this->assertStringNotContainsString('$_GET', $clickVars);
        $this->assertFileDoesNotExist(app_path('Support/LegacyClickVars.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/ClickVars.php'));

        $this->assertSame(
            ['sub1' => 'alpha', 'sub2' => '', 'sub3' => '', 'sub4' => '', 'sub5' => 'omega'],
            \App\Support\ClickVariables::processUrlToSubIDArray('/offer?sub1=alpha&s5=omega')
        );
        $this->assertSame(
            'https://example.test/' . base64_encode('value'),
            \App\Support\ClickVariables::checkForBase64('https://example.test/<base64>value</base64>')
        );
    }

    public function test_runtime_click_search_reads_use_laravel_support(): void
    {
        $controller = File::get(app_path('Http/Controllers/ClickSearchController.php'));

        $this->assertStringContainsString('App\\Support\\ClickSearcher', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickSearcher', $controller);
        $this->assertStringNotContainsString('App\\Support\\LegacyClickSearcher', $controller);
        $this->assertFileExists(app_path('Support/ClickSearcher.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyClickSearcher.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/ClickSearcher.php'));
    }

    public function test_runtime_conversions_use_laravel_support(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
            app_path('Support/Tracking/Events/ConversionRegistrationEvent.php'),
            app_path('Support/Tracking/Events/DeductionRegistrationEvent.php'),
            app_path('Support/OfferDomain/SaleLog.php'),
            app_path('Support/ReferralRegister.php'),
        ] as $path) {
            $contents = File::get($path);

            if ($path === app_path('Support/ReferralRegister.php')) {
                $this->assertStringContainsString('Conversion::', $contents);
            } else {
                $this->assertStringContainsString('App\\Support\\Conversion', $contents);
            }
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\Conversion', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyConversion as Conversion', $contents);
        }

        $this->assertFileExists(app_path('Support/Conversion.php'));
        $this->assertFileExists(app_path('Support/ReferralRegister.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyConversion.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/Conversion.php'));
        $this->assertFileDoesNotExist(base_path('src/User/ReferralRegister.php'));

        $conversion = File::get(app_path('Support/Conversion.php'));
        $this->assertStringContainsString('DatabaseConnection::getInstance()', $conversion);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $conversion);

        $referralRegister = File::get(app_path('Support/ReferralRegister.php'));

        $this->assertStringContainsString('DatabaseConnection::getInstance()', $referralRegister);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $referralRegister);
    }

    public function test_runtime_pending_conversions_use_laravel_support(): void
    {
        foreach ([
            app_path('Http/Controllers/ChatLogController.php'),
            app_path('Support/Tracking/Events/ConversionRegistrationEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\PendingConversion', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\PendingConversion', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyPendingConversion', $contents);
        }

        $this->assertFileExists(app_path('Support/PendingConversion.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyPendingConversion.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/PendingConversion.php'));
    }

    public function test_modern_click_id_reads_use_legacy_uid_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/ClickIdToolController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            app_path('Support/Tracking/URLTagReplacers/TYSVariables.php'),
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
            app_path('Support/Tracking/Events/Listeners/ConversionListener.php'),
            app_path('Support/Tracking/Events/Listeners/DeductionListener.php'),
            app_path('Support/Tracking/Events/Listeners/FreeSignUpListener.php'),
            app_path('Support/Tracking/Events/UrlEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\ClickIdCodec as UID', $contents);
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

        $encodedClickId = \App\Support\ClickIdCodec::encode('1029384756');
        $this->assertSame('1029384756', \App\Support\ClickIdCodec::decode($encodedClickId));
        $this->assertFileDoesNotExist(app_path('Support/LegacyUid.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/UID.php'));

        $processor = new \App\Support\Tracking\URLProcessor(
            'https://example.test/#affid#/#offid#/#clickid#/#user#/#sub1#/<base64>secret</base64>'
        );
        $processor->addTagReplacer(new \App\Support\Tracking\URLTagReplacers\TYSVariables(4, 'alice', 'encoded', 9));
        $processor->addTagReplacer(new \App\Support\Tracking\URLTagReplacers\SubVariables(['sub1' => 'campaign']));
        $processor->addTagReplacer(new \App\Support\Tracking\URLTagReplacers\Base64());
        $processor->processURL();
        $this->assertSame(
            'https://example.test/4/9/encoded/alice/campaign/' . base64_encode('secret'),
            $processor->url
        );

        foreach ([
            'src/Clicks/URLProcessor.php',
            'src/Clicks/URLTagReplacers/Base64.php',
            'src/Clicks/URLTagReplacers/SubVariables.php',
            'src/Clicks/URLTagReplacers/TYSVariables.php',
            'src/Clicks/URLTagReplacers/TagReplacer.php',
        ] as $retiredPath) {
            $this->assertFileDoesNotExist(base_path($retiredPath));
        }
    }

    public function test_modern_lander_reads_use_legacy_lander_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/IndexController.php'),
            app_path('Http/Controllers/LanderController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Lander', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Lander', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/System/Lander.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyLander.php'));
        $lander = File::get(app_path('Support/Lander.php'));

        $this->assertStringContainsString('NativeRequest::', $lander);
        $this->assertStringNotContainsString('$_GET', $lander);
        $this->assertStringNotContainsString('$_SERVER', $lander);
    }

    public function test_modern_payout_reads_use_legacy_payouts_boundary(): void
    {
        foreach ([
            app_path('Click.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/Report/ConversionReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
            app_path('Support/Conversion.php'),
            app_path('Support/Report/Repositories/Employee/AdminEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Employee/GodEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Employee/ManagerEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Offer/AdminOfferRepository.php'),
            app_path('Support/Report/Repositories/Offer/GodOfferRepository.php'),
            app_path('Support/Report/Repositories/Offer/ManagerOfferRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\OfferDomain\\Payouts', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Payouts', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/Offer/Payouts.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyPayouts.php'));
    }

    public function test_modern_adjustment_log_reads_use_legacy_adjustments_log_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\OfferDomain\\AdjustmentsLog', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/Offer/AdjustmentsLog.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyAdjustmentsLog.php'));
        $adjustmentsLog = File::get(app_path('Support/OfferDomain/AdjustmentsLog.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $adjustmentsLog);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $adjustmentsLog);
    }

    public function test_modern_sale_log_reads_use_legacy_sale_log_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/ChatLogController.php'));

        $this->assertStringContainsString('App\\Support\\OfferDomain\\SaleLog', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\SaleLog', $controller);

        $this->assertFileDoesNotExist(base_path('src/Offer/SaleLog.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacySaleLog.php'));
        $saleLog = File::get(app_path('Support/OfferDomain/SaleLog.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $saleLog);
        $this->assertStringContainsString('App\\Services\\SaleLogImageStorage', $saleLog);
        $this->assertStringNotContainsString("config('filesystems.sale_log_directory')", $saleLog);
        $this->assertStringNotContainsString('SALE_LOG_DIRECTORY', $saleLog);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $saleLog);
    }

    public function test_runtime_date_reads_use_laravel_date_helper(): void
    {
        foreach ([
            app_path('BonusOffer.php'),
            app_path('Console/Commands/AggregateReportData.php'),
            app_path('Console/Commands/PayoutLogsRun.php'),
            app_path('Http/Controllers/Report/ReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DateHelper as Date', $contents);
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

            $this->assertStringContainsString('App\\Support\\DateHelper', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Date', $contents);
        }

        $dateHelper = File::get(app_path('Support/DateHelper.php'));
        $this->assertStringContainsString('NativeRequest::cookie', $dateHelper);
        $this->assertStringNotContainsString('$_COOKIE', $dateHelper);
        $this->assertFileDoesNotExist(app_path('Support/LegacyDate.php'));
        $this->assertFileDoesNotExist(base_path('src/Table/Date.php'));
    }

    public function test_runtime_pagination_uses_laravel_helper(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/UserController.php'),
            app_path('Support/OfferDomain/View.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\PaginationHelper as Paginate', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Paginate', $contents);
        }

        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Paginate', $offerController);

        $paginate = File::get(app_path('Support/PaginationHelper.php'));
        $this->assertStringContainsString('NativeRequest::query', $paginate);
        $this->assertStringContainsString('NativeRequest::requestUri', $paginate);
        $this->assertStringNotContainsString('$_GET', $paginate);
        $this->assertStringNotContainsString('$_SERVER', $paginate);
        $this->assertFileDoesNotExist(app_path('Support/LegacyPaginate.php'));
        $this->assertFileDoesNotExist(base_path('src/Table/Paginate.php'));

        $pagination = new \App\Support\PaginationHelper(25, 51);
        $this->assertSame(1, $pagination->current_page);
        $this->assertSame(3.0, $pagination->page_total());
        $this->assertSame(0, $pagination->offset());
        $this->assertFalse($pagination->has_previous());
        $this->assertTrue($pagination->has_next());
    }

    public function test_runtime_query_assignments_use_laravel_helper(): void
    {
        foreach ([
            app_path('Support/OfferDomain/Create.php'),
            app_path('Support/OfferDomain/Update.php'),
            app_path('Support/Report/Filters/ClickLink.php'),
            app_path('Support/UserDomain/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\QueryAssignments as Assignments', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Assignments', $contents);
        }

        $assignments = File::get(app_path('Support/QueryAssignments.php'));
        $this->assertStringContainsString('NativeRequest::queryAll', $assignments);
        $this->assertStringNotContainsString('$_GET', $assignments);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAssignments.php'));
        $this->assertFileDoesNotExist(base_path('src/Table/Assignments.php'));

        $queryAssignments = new \App\Support\QueryAssignments([
            'page' => 2,
            'status' => 'active',
            '!offer' => null,
        ], false, false);
        $this->assertTrue($queryAssignments->has('offer'));
        $this->assertSame('!', $queryAssignments->get('offer'));
        $this->assertSame('?page=2&status=active&offer=!', $queryAssignments->buildAssignments());
        $this->assertSame('?page=2&offer=!', $queryAssignments->buildAssignments(['status']));
        $this->assertSame('{"page":2,"offer":"!"}', $queryAssignments->buildJSONArray(['status']));
    }

    public function test_modern_tree_reads_use_legacy_tree_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/UserController.php'),
            app_path('Observers/UserObserver.php'),
            app_path('Support/OfferDomain/RepHasOffer.php'),
            app_path('Support/OfferDomain/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\UserDomain\\Tree', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Tree', $contents);
        }

        $this->assertFileDoesNotExist(app_path('Support/LegacyTree.php'));

        $tree = File::get(app_path('Support/UserDomain/Tree.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $tree);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $tree);
    }

    public function test_modern_admin_login_state_uses_laravel_boundaries(): void
    {
        $dashboardShell = File::get(resource_path('views/layouts/dashboard-shell.blade.php'));

        $this->assertStringContainsString('App\\Support\\RequestContext::hasQuery', $dashboardShell);
        $this->assertStringContainsString('adminLogin=1', $dashboardShell);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\AdminLogin', $dashboardShell);

        $this->assertFileDoesNotExist(app_path('Support/LegacyAdminLogin.php'));
        $this->assertFileDoesNotExist(base_path('src/User/AdminLogin.php'));
    }

    public function test_report_views_do_not_reference_retired_jquery_table_assets(): void
    {
        foreach (File::allFiles(resource_path('views/report')) as $file) {
            $path = $file->getPathname();
            $contents = File::get($path);

            foreach (['tables.js', 'jquery_2.1.3_jquery.min.js', 'jquery.tablesorter.min.js'] as $asset) {
                $this->assertStringNotContainsString($asset, $contents, "{$path} should not load retired {$asset}.");
            }
        }
    }

    public function test_modern_notification_boundary_is_isolated_from_layouts(): void
    {
        $layout = File::get(resource_path('views/layouts/dashboard-shell.blade.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Notify', $layout);

        $this->assertFileDoesNotExist(app_path('Support/LegacyNotify.php'));
        $this->assertFileDoesNotExist(base_path('src/System/Notify.php'));
    }

    public function test_runtime_report_views_use_laravel_owned_formatters(): void
    {
        foreach ([
            resource_path('views/report/employee.blade.php'),
            resource_path('views/report/offer/admin.blade.php'),
            resource_path('views/report/offer/affiliate.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\Formats\\Html', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Formats\\HTML', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyReportHtml', $contents);
        }

        $aggregateController = File::get(app_path('Http/Controllers/Report/AggregateReportController.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Formats\\HTML', $aggregateController);

        $reportHtml = File::get(app_path('Support/Report/Formats/Html.php'));

        $this->assertStringContainsString('NativeRequest::query(', $reportHtml);
        $this->assertStringNotContainsString('$_GET', $reportHtml);
        $this->assertFileDoesNotExist(app_path('Support/LegacyReportHtml.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Formats/HTML.php'));
    }

    public function test_retired_click_offer_report_path_stays_removed(): void
    {
        $controller = File::get(app_path('Http/Controllers/Report/ClickReportController.php'));

        $this->assertStringNotContainsString('showOfferClicks', $controller);
        $this->assertStringNotContainsString('showManagersClicks', $controller);
        $this->assertStringNotContainsString('LegacyOfferReport', $controller);
        $this->assertFileDoesNotExist(app_path('Support/LegacyOfferReport.php'));
    }

    public function test_offer_report_role_dispatch_helpers_are_not_public_actions(): void
    {
        $controller = File::get(app_path('Http/Controllers/Report/OfferReportController.php'));

        foreach (['god', 'admin', 'manager', 'affiliate'] as $method) {
            $this->assertStringContainsString("private function {$method}(", $controller);
            $this->assertStringNotContainsString("public function {$method}(", $controller);
        }
    }

    public function test_runtime_report_controllers_use_laravel_reporter(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\Reporter', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Reporter', $contents);
            $this->assertStringNotContainsString('App\\Support\\LegacyReporter', $contents);
        }

        $this->assertFileExists(app_path('Support/Report/Reporter.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyReporter.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Reporter.php'));
    }

    public function test_runtime_report_controllers_use_laravel_owned_filters(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Support/Report/AffiliatePayout.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\Filters', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Filters', $contents);
        }

        foreach ([
            app_path('Support/LegacyClickLinkFilter.php'),
            app_path('Support/LegacyDeductionColumnFilter.php'),
            app_path('Support/LegacyDollarSignFilter.php'),
            app_path('Support/LegacyEarningPerClickFilter.php'),
            app_path('Support/LegacyTotalFilter.php'),
            app_path('Support/LegacyUserToolTipFilter.php'),
        ] as $path) {
            $this->assertFileDoesNotExist($path);
        }

        foreach (['ClickLink', 'DeductionColumnFilter', 'DollarSign', 'EarningPerClick', 'Filter', 'Total', 'UserToolTip'] as $class) {
            $this->assertFileExists(app_path("Support/Report/Filters/{$class}.php"));
        }
    }

    public function test_modern_report_controllers_use_legacy_report_object_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/PayoutReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\AffiliatePayout', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Affiliate;', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\AffiliatePayout', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\BlackList', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\BlackListRepository', $contents);
        }

        $this->assertFileExists(app_path('Support/Report/AffiliatePayout.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyAffiliatePayoutReport.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/AffiliatePayout.php'));

        $offerReportController = File::get(app_path('Http/Controllers/Report/OfferReportController.php'));
        $this->assertStringContainsString("DB::table('click_bonus')", $offerReportController);
        $this->assertStringNotContainsString('LegacyAffiliateReport', $offerReportController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAffiliateReport.php'));

        $blacklistController = File::get(app_path('Http/Controllers/Report/BlackListReportController.php'));
        $this->assertStringContainsString("DB::table('rep')", $blacklistController);
        $this->assertStringNotContainsString('LegacyBlackList', $blacklistController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyBlackListReport.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyBlackListRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/BlackList.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/BlackListRepository.php'));
    }

    public function test_modern_report_controllers_use_legacy_database_connection_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/Report/OfferReportController.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $controller);

        $this->assertFileDoesNotExist(base_path('src/Database/DatabaseConnection.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyDatabaseConnection.php'));

        foreach ([
            app_path('Support/Report/AffiliatePayout.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_legacy_system_helpers_use_legacy_database_connection_boundary(): void
    {
        foreach ([
            app_path('Support/RuntimeCompany.php'),
            app_path('Support/Connection.php'),
            app_path('Support/GlobalFunctions.php'),
            app_path('Support/GlobalLogging.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }

        foreach ([
            app_path('Support/RuntimeCompany.php'),
            app_path('Support/Connection.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\NativeSession', $contents);
            $this->assertStringNotContainsString('$_SESSION', $contents);
        }

        foreach ([
            app_path('Support/RuntimeCompany.php'),
            app_path('Support/Connection.php'),
            app_path('Support/GlobalFunctions.php'),
            app_path('Support/GlobalLogging.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\NativeRequest', $contents);
            $this->assertStringNotContainsString('$_GET', $contents);
            $this->assertStringNotContainsString('$_POST', $contents);
            $this->assertStringNotContainsString('$_SERVER', $contents);
        }

        $legacyDatabaseConfig = File::get(app_path('Services/LegacyDatabaseConfig.php'));
        $databaseConnection = File::get(app_path('Support/DatabaseConnection.php'));
        $runtimeCompany = File::get(app_path('Support/RuntimeCompany.php'));
        $connection = File::get(app_path('Support/Connection.php'));
        $indexController = File::get(app_path('Http/Controllers/IndexController.php'));
        foreach (['DBUpdater.php', 'Database.php', 'GeoIPUpdater.php', 'SFS.php', 'Setup.php'] as $retiredFile) {
            $this->assertFileDoesNotExist(base_path('src/System/'.$retiredFile));
        }

        $this->assertStringContainsString('configIsAvailable()', $legacyDatabaseConfig);
        $this->assertStringContainsString("config(\"database.connections.{\$connection}.{\$key}\")", $legacyDatabaseConfig);
        $this->assertStringContainsString("self::databaseConfig()['connections'][\$connection]", $legacyDatabaseConfig);
        $this->assertStringContainsString("require base_path('config/database.php')", $legacyDatabaseConfig);
        $this->assertStringContainsString('LegacyDatabaseConfig', $databaseConnection);
        $this->assertStringContainsString('LegacyDatabaseConfig', $runtimeCompany);
        $this->assertStringContainsString('LegacyDatabaseConfig', $connection);
        $this->assertStringContainsString('LegacyDatabaseConfig', $indexController);
        $this->assertStringNotContainsString("config('database.connections.mysql.database')", $indexController);
        $this->assertStringNotContainsString('DB_DATABASE', $databaseConnection);
        $this->assertStringNotContainsString('DB_DATABASE', $runtimeCompany);
        $this->assertStringNotContainsString('DB_DATABASE', $connection);
        $this->assertStringNotContainsString('DB_DATABASE', $indexController);
        $this->assertStringNotContainsString('DB_HOST', $databaseConnection);
        $this->assertStringNotContainsString('DB_HOST', $connection);
        $this->assertStringNotContainsString('MASTER_DB_', $databaseConnection);
    }

    public function test_runtime_offer_reports_use_laravel_repositories(): void
    {
        foreach ([
            app_path('Http/Controllers/ExportDataController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\Repositories\\Offer', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Offer', $contents);
        }

        foreach ([
            app_path('Support/LegacyAdminOfferRepository.php'),
            app_path('Support/LegacyAffiliateOfferRepository.php'),
            app_path('Support/LegacyGodOfferRepository.php'),
            app_path('Support/LegacyManagerOfferRepository.php'),
        ] as $path) {
            $this->assertFileDoesNotExist($path);
        }

        foreach (['AdminOfferRepository', 'AffiliateOfferRepository', 'GodOfferRepository', 'ManagerOfferRepository'] as $class) {
            $this->assertFileExists(app_path("Support/Report/Repositories/Offer/{$class}.php"));
        }
    }

    public function test_runtime_employee_reports_use_laravel_repositories(): void
    {
        foreach ([
            app_path('Console/Commands/AggregateReportData.php'),
            app_path('Console/Commands/PayoutLogsRun.php'),
            app_path('Http/Controllers/ExportDataController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Report\\Repositories\\Employee', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\AdminEmployeeRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\GodEmployeeRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Employee\\ManagerEmployeeRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Repository', $contents);
        }

        foreach ([
            app_path('Support/LegacyAdminEmployeeRepository.php'),
            app_path('Support/LegacyGodEmployeeRepository.php'),
            app_path('Support/LegacyManagerEmployeeRepository.php'),
        ] as $path) {
            $this->assertFileDoesNotExist($path);
        }

        foreach (['AdminEmployeeRepository', 'GodEmployeeRepository', 'ManagerEmployeeRepository'] as $class) {
            $this->assertFileExists(app_path("Support/Report/Repositories/Employee/{$class}.php"));
        }
    }

    public function test_modern_misc_report_repositories_use_legacy_boundaries(): void
    {
        $subReportController = File::get(app_path('Http/Controllers/Report/SubReportController.php'));

        $this->assertStringContainsString("DB::table('clicks')", $subReportController);
        $this->assertStringContainsString("DB::table('conversions')", $subReportController);
        $this->assertStringNotContainsString('LegacySubVarRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AdjustmentsLogRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AdvertiserRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AffiliateChatLogRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AggregateReportRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\PayoutLogRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\SaleLogRepository', $subReportController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\SubVarRepository', $subReportController);

        $subReportView = File::get(resource_path('views/report/sub.blade.php'));

        $this->assertStringContainsString('@foreach($report as $row)', $subReportView);
        $this->assertStringNotContainsString('LegacyReportHtml', $subReportView);
        $this->assertFalse(File::exists(app_path('Support/LegacySubVarRepository.php')));
        $this->assertFalse(File::exists(base_path('src/Report/Repositories/SubVarRepository.php')));

        $referralRepository = File::get(app_path('Support/Report/Repositories/ReferralRepository.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $referralRepository);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $referralRepository);

        $aggregateController = File::get(app_path('Http/Controllers/Report/AggregateReportController.php'));
        $this->assertStringContainsString("DB::table('aggregate_reports')", $aggregateController);
        $this->assertStringNotContainsString('LegacyAggregateReportRepository', $aggregateController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAggregateReportRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/AggregateReportRepository.php'));

        $adjustmentsController = File::get(app_path('Http/Controllers/Report/AdjustmentsReportController.php'));
        $this->assertStringContainsString("DB::table('adjustments_log')", $adjustmentsController);
        $this->assertStringNotContainsString('LegacyAdjustmentsLogRepository', $adjustmentsController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAdjustmentsLogRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/AdjustmentsLogRepository.php'));

        $payoutController = File::get(app_path('Http/Controllers/Report/PayoutReportController.php'));
        $this->assertStringContainsString('PayoutLog::query()', $payoutController);
        $this->assertStringNotContainsString('LegacyPayoutLogRepository', $payoutController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyPayoutLogRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/PayoutLogRepository.php'));

        $advertiserController = File::get(app_path('Http/Controllers/Report/AdvertiserReportController.php'));
        $this->assertStringContainsString("DB::table('campaigns')", $advertiserController);
        $this->assertStringNotContainsString('LegacyAdvertiserRepository', $advertiserController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAdvertiserRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/AdvertiserRepository.php'));

        $chatLogController = File::get(app_path('Http/Controllers/Report/ChatLogReportController.php'));
        $this->assertStringContainsString("DB::table('rep')", $chatLogController);
        $this->assertStringNotContainsString('LegacySaleLogRepository', $chatLogController);
        $this->assertFileDoesNotExist(app_path('Support/LegacySaleLogRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/SaleLogRepository.php'));
        $this->assertStringNotContainsString('LegacyAffiliateChatLogRepository', $chatLogController);
        $this->assertFileDoesNotExist(app_path('Support/LegacyAffiliateChatLogRepository.php'));
        $this->assertFileDoesNotExist(base_path('src/Report/Repositories/AffiliateChatLogRepository.php'));
    }

    public function test_runtime_tracking_parameter_reads_use_laravel_helper(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            app_path('Support/Tracking/Events/Listeners/BonusListener.php'),
            app_path('Support/Tracking/Events/Listeners/ClickListener.php'),
            app_path('Support/Tracking/Events/Listeners/Listener.php'),
            app_path('Support/OfferDomain/Caps.php'),
            app_path('Support/OfferDomain/Rules.php'),
            app_path('Support/OfferDomain/Rules/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\TrackingParameters', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\TrackingParameters', $contents);
        }

        $parameters = \App\Support\TrackingParameters::normalize(['rid' => 42, 'oid' => 7, 's1' => 'alpha']);
        $this->assertSame(42, $parameters['repid']);
        $this->assertSame(7, $parameters['offerid']);
        $this->assertSame('alpha', $parameters['sub1']);
        $this->assertSame(99, \App\Support\TrackingParameters::get(['repid' => 99, 'rid' => 42], 'repid'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyTrackingParameters.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/TrackingParameters.php'));

        foreach ([
            app_path('Support/Tracking/Events/Listeners/BonusListener.php'),
            app_path('Support/Tracking/Events/Listeners/ClickListener.php'),
            app_path('Support/Tracking/Events/Listeners/ConversionListener.php'),
            app_path('Support/Tracking/Events/Listeners/DeductionListener.php'),
            app_path('Support/Tracking/Events/Listeners/FreeSignUpListener.php'),
            app_path('Support/Tracking/Events/Listeners/Listener.php'),
            app_path('Support/OfferDomain/Caps.php'),
            app_path('Support/OfferDomain/Rules.php'),
            app_path('Support/OfferDomain/Rules/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\NativeRequest', $contents);
            $this->assertStringNotContainsString('$_GET', $contents);
            $this->assertStringNotContainsString('$_SERVER', $contents);
        }

        $noneUnique = File::get(app_path('Support/OfferDomain/Rules/NoneUnique.php'));

        $this->assertStringNotContainsString('$_COOKIE', $noneUnique);
    }

    public function test_tracking_event_pipeline_is_laravel_owned(): void
    {
        $controller = File::get((new ReflectionClass(IndexController::class))->getFileName());
        $this->assertStringContainsString('App\\Support\\Tracking\\PostBackUrlEventHandler', $controller);
        $this->assertStringContainsString('App\\Support\\Tracking\\Events\\ClickRegistrationEvent', $controller);
        $this->assertStringNotContainsString('App\\Support\\LegacyPostBackURLEventHandler', $controller);
        $this->assertStringNotContainsString('App\\Support\\LegacyClickRegistrationEvent', $controller);

        foreach ([
            'BonusRegistrationEvent.php',
            'ClickRegistrationEvent.php',
            'ConversionRegistrationEvent.php',
            'DeductionRegistrationEvent.php',
            'FreeSignUpRegistrationEvent.php',
            'UrlEvent.php',
        ] as $file) {
            $this->assertFileExists(app_path("Support/Tracking/Events/{$file}"));
        }

        foreach ([
            'BonusListener.php',
            'ClickListener.php',
            'ConversionListener.php',
            'DeductionListener.php',
            'FreeSignUpListener.php',
            'Listener.php',
        ] as $file) {
            $this->assertFileExists(app_path("Support/Tracking/Events/Listeners/{$file}"));
        }

        $this->assertFileExists(app_path('Support/Tracking/PostBackUrlEventHandler.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyPostBackURLEventHandler.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyClickRegistrationEvent.php'));
        $this->assertFileDoesNotExist(base_path('src/Clicks/PostBackURLEventHandler.php'));

        $clickRegistrationEvent = File::get(app_path('Support/Tracking/Events/ClickRegistrationEvent.php'));
        $clickListener = File::get(app_path('Support/Tracking/Events/Listeners/ClickListener.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $clickRegistrationEvent);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $clickRegistrationEvent);
        $this->assertStringContainsString('App\\Support\\NativeRequest', $clickRegistrationEvent);
        $this->assertStringContainsString('NativeRequest::referrer()', $clickRegistrationEvent);
        $this->assertStringContainsString('NativeRequest::userAgent()', $clickRegistrationEvent);
        $this->assertStringNotContainsString('$_GET', $clickRegistrationEvent);
        $this->assertStringNotContainsString('$_SERVER', $clickRegistrationEvent);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_REFERER')", $clickRegistrationEvent);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_USER_AGENT')", $clickRegistrationEvent);
        $this->assertStringContainsString('NativeRequest::clientIp()', $clickListener);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_CLIENT_IP')", $clickListener);
        $this->assertStringNotContainsString("NativeRequest::server('HTTP_X_FORWARDED_FOR')", $clickListener);
    }

    public function test_modern_request_host_and_ip_reads_use_request_context_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/IndexController.php'),
            app_path('Http/Controllers/NotificationController.php'),
            app_path('Http/Controllers/OfferController.php'),
            resource_path('views/layouts/dashboard-shell.blade.php'),
            resource_path('views/offer/manage.blade.php'),
            resource_path('views/offer/url-form.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\RequestContext', $contents);
            $this->assertStringNotContainsString('request()->getHttpHost()', $contents);
            $this->assertStringNotContainsString('request()->getHost()', $contents);
            $this->assertStringNotContainsString('request()->getSchemeAndHttpHost()', $contents);
            $this->assertStringNotContainsString('$request->getHttpHost()', $contents);
            $this->assertStringNotContainsString('$request->getHost()', $contents);
            $this->assertStringNotContainsString('$request->getSchemeAndHttpHost()', $contents);
            $this->assertStringNotContainsString("request()->server('SERVER_ADDR')", $contents);
            $this->assertStringNotContainsString('$request->server(', $contents);
            $this->assertStringNotContainsString("request()->has('adminLogin')", $contents);
            $this->assertStringNotContainsString('request()->path()', $contents);
            $this->assertStringNotContainsString("request('url'", $contents);
        }
    }

    public function test_user_directory_views_receive_simple_request_state_from_controller(): void
    {
        $controller = File::get(app_path('Http/Controllers/UserController.php'));

        foreach ([
            "'role' => \$role",
            "'showInactive' => \$showInactive",
            "'rowsPerPage'",
        ] as $expectedPattern) {
            $this->assertStringContainsString($expectedPattern, $controller);
        }

        foreach ([
            resource_path('views/user/manage.blade.php'),
            resource_path('views/user/managers-affiliates.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString("request('role'", $contents);
            $this->assertStringNotContainsString("request('showInactive'", $contents);
            $this->assertStringNotContainsString("request()->query('rpp'", $contents);
        }
    }

    public function test_shared_report_request_reads_use_request_context_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/ExportDataController.php'),
            app_path('Http/Controllers/Report/ReportController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/Report/ConversionReportController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\RequestContext', $contents);
            $this->assertStringNotContainsString('request()->', $contents);
            $this->assertStringNotContainsString('\\request()->', $contents);
            $this->assertStringNotContainsString('request()', $contents);
        }
    }

    public function test_modern_offer_and_user_controllers_do_not_use_global_request_helpers(): void
    {
        foreach ([
            app_path('Http/Controllers/OfferController.php'),
            app_path('Http/Controllers/UserController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('request()->', $contents);
            $this->assertDoesNotMatchRegularExpression('/(?<![A-Za-z_])request\(/', $contents);
        }

        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $this->assertStringNotContainsString('Facades\\Request', $offerController);
        $this->assertStringNotContainsString('Facades\\Session', $offerController);
    }

    public function test_multi_value_current_user_consumers_use_session_snapshots(): void
    {
        foreach ([
            app_path('Providers/AppServiceProvider.php'),
            app_path('Http/Controllers/BonusController.php'),
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/DashboardController.php'),
            app_path('Http/Controllers/GlobalPostbackController.php'),
            app_path('Http/Controllers/NotificationController.php'),
            app_path('Http/Controllers/OfferController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/ClickReportController.php'),
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            app_path('Http/Controllers/UserController.php'),
            app_path('Http/Controllers/Sms/SmsController.php'),
            app_path('Services/Repositories/Offer/OfferAffiliateClicksRepository.php'),
            app_path('Services/Repositories/Offer/OfferClicksRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('CurrentUserSession::snapshot()', $contents);
        }

        $boundary = File::get(app_path('Support/CurrentUserSession.php'));
        $this->assertStringContainsString('public static function snapshot(): CurrentUserContext', $boundary);
        $this->assertStringNotContainsString('Session::user()', $boundary);
    }

    public function test_click_model_uses_the_request_context_boundary(): void
    {
        $contents = File::get(app_path('Click.php'));

        $this->assertStringContainsString('App\\Support\\RequestContext', $contents);
        $this->assertStringNotContainsString('request()->', $contents);
        $this->assertDoesNotMatchRegularExpression('/(?<![A-Za-z_])request\(/', $contents);
    }

    public function test_blade_views_do_not_use_global_request_helpers(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = File::get($file->getPathname());

            $this->assertStringNotContainsString('request()->', $contents, $file->getPathname());
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![A-Za-z_])request\(/',
                $contents,
                $file->getPathname()
            );
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![A-Za-z_])session\(/',
                $contents,
                $file->getPathname()
            );
        }
    }

    public function test_modern_ip_blacklist_reads_use_legacy_ip_blacklist_boundary(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            (new ReflectionClass(IPBlacklistController::class))->getFileName(),
            app_path('Support/Tracking/Events/ClickRegistrationEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\IPBlackList', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\IPBlackList', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/System/IPBlackList.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyIPBlackList.php'));
        $ipBlackList = File::get(app_path('Support/IPBlackList.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $ipBlackList);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $ipBlackList);

    }

    public function test_modern_sale_log_image_uploads_use_legacy_images_uploader_boundary(): void
    {
        $controller = File::get((new ReflectionClass(ChatLogController::class))->getFileName());

        $this->assertStringContainsString('App\\Support\\ImagesUploader', $controller);
        $this->assertStringContainsString('App\\Services\\SaleLogImageStorage', $controller);
        $this->assertStringNotContainsString("config('filesystems.sale_log_directory')", $controller);
        $this->assertStringNotContainsString('SALE_LOG_DIRECTORY', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Files\\ImagesUploader', $controller);

        $this->assertFileDoesNotExist(base_path('src/System/Files/ImagesUploader.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyImagesUploader.php'));
        $imagesUploader = File::get(app_path('Support/ImagesUploader.php'));

        $this->assertStringContainsString('App\\Services\\SaleLogImageStorage', $imagesUploader);
        $this->assertStringNotContainsString("config('filesystems.sale_log_directory')", $imagesUploader);
        $this->assertStringNotContainsString('SALE_LOG_DIRECTORY', $imagesUploader);

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats', $imagesUploader);
    }

    public function test_modern_notification_reads_use_legacy_notifications_boundary(): void
    {
        foreach ([
            (new ReflectionClass(AppServiceProvider::class))->getFileName(),
            (new ReflectionClass(OfferController::class))->getFileName(),
            app_path('Support/OfferDomain/RepHasOffer.php'),
            app_path('Support/UserDomain/AffiliateSignUp.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Notifications', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Notifications', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/System/Notifications.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyNotifications.php'));
        $notifications = File::get(app_path('Support/Notifications.php'));

        $this->assertStringContainsString('App\\Support\\DatabaseConnection', $notifications);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $notifications);
        $this->assertStringContainsString('NativeRequest::', $notifications);
        $this->assertStringNotContainsString('$_POST', $notifications);
        $this->assertStringNotContainsString('$_SERVER', $notifications);

    }

    public function test_dashboard_navigation_uses_legacy_navbar_boundary(): void
    {
        $provider = File::get((new ReflectionClass(AppServiceProvider::class))->getFileName());

        $this->assertStringContainsString('App\\Support\\NavBar', $provider);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\NavBar', $provider);

        $this->assertFileDoesNotExist(base_path('src/System/NavBar.php'));
        $this->assertFileDoesNotExist(app_path('Support/LegacyNavBar.php'));
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats',
            File::get(app_path('Support/NavBar.php'))
        );

        $navBar = File::get(app_path('Support/NavBar.php'));

        $this->assertStringContainsString('App\\Support\\NativeRequest', $navBar);
        $this->assertStringNotContainsString('$_SERVER', $navBar);
    }

    public function test_legacy_report_domain_helpers_use_current_session_boundary(): void
    {
        foreach ([
            app_path('Support/Report/Formats/Html.php'),
            app_path('Support/Report/Repositories/BannedUsersRepository.php'),
            app_path('Support/Report/Repositories/Employee/AdminEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Employee/GodEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Employee/ManagerEmployeeRepository.php'),
            app_path('Support/Report/Repositories/Offer/AdminOfferRepository.php'),
            app_path('Support/Report/Repositories/Offer/GodOfferRepository.php'),
            app_path('Support/Report/Repositories/Offer/ManagerOfferRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            if ($path === app_path('Support/Conversion.php')) {
                $this->assertStringContainsString('CurrentUserSession::', $contents);
            } else {
                $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            }
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_legacy_user_offer_click_and_notification_helpers_use_current_session_boundary(): void
    {
        foreach ([
            app_path('Support/Conversion.php'),
            app_path('Support/OfferDomain/Campaigns.php'),
            app_path('Support/OfferDomain/Create.php'),
            app_path('Support/OfferDomain/Offer.php'),
            app_path('Support/OfferDomain/RepHasOffer.php'),
            app_path('Support/OfferDomain/SaleLog.php'),
            app_path('Support/OfferDomain/Update.php'),
            app_path('Support/OfferDomain/View.php'),
            app_path('Support/Notifications.php'),
            app_path('Support/UserDomain/Bonus.php'),
            app_path('Support/UserDomain/Create.php'),
            app_path('Support/UserDomain/Login.php'),
            app_path('Support/UserDomain/Permissions.php'),
            app_path('Support/UserDomain/Referrals.php'),
            app_path('Support/UserDomain/Salary.php'),
            app_path('Support/UserDomain/Update.php'),
            app_path('Support/UserDomain/User.php'),
        ] as $path) {
            $contents = File::get($path);

            if ($path === app_path('Support/Conversion.php')) {
                $this->assertStringContainsString('CurrentUserSession::', $contents);
            } else {
                $this->assertStringContainsString('App\\Support\\CurrentUserSession', $contents);
            }
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }

        $this->assertFileDoesNotExist(base_path('src/Table/ReportBase.php'));
        $this->assertFileDoesNotExist(base_path('src/Table/Functions.php'));

        $reportBase = File::get(app_path('Support/ReportBase.php'));
        $this->assertStringContainsString('CurrentUserSession::permissions()', $reportBase);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $reportBase);

        $repHasOffer = File::get(app_path('Support/OfferDomain/RepHasOffer.php'));

        $this->assertStringNotContainsString('$_SESSION', $repHasOffer);
    }

    public function test_no_legacy_post_php_routes_or_csrf_exceptions_remain(): void
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

        $this->assertTrue($legacyPostUris->isEmpty(), $legacyPostUris->implode(', '));
        $this->assertTrue($legacyPhpCsrfExceptions->isEmpty(), $legacyPhpCsrfExceptions->implode(', '));
    }

    public function test_runtime_branding_reads_use_shared_boundaries(): void
    {
        foreach ([
            app_path(),
            base_path('src'),
            resource_path('views'),
        ] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                $path = $file->getPathname();

                if (in_array($path, [
                    app_path('Services/BrandingLabels.php'),
                    app_path('Services/LoginBranding.php'),
                ], true)) {
                    continue;
                }

                $contents = File::get($path);

                foreach ([
                    "config('branding.account.",
                    "config('branding.affiliate.",
                    "config('branding.login.",
                ] as $forbiddenPattern) {
                    $this->assertStringNotContainsString(
                        $forbiddenPattern,
                        $contents,
                        "{$path} should read runtime branding values through shared branding boundaries."
                    );
                }
            }
        }
    }

    public function test_legacy_source_directory_contains_no_php_files(): void
    {
        $phpFiles = collect(File::allFiles(base_path('src')))
            ->filter(fn ($file) => $file->getExtension() === 'php');

        $this->assertCount(0, $phpFiles);
    }

    public function test_sms_pool_runtime_config_reads_use_shared_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/SmsOrderController.php'),
            app_path('Services/SmsPoolService.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('SmsPool', $contents);
            $this->assertStringNotContainsString("config('services.smspool.", $contents);
        }
    }

    public function test_runtime_source_reads_configuration_instead_of_env_directly(): void
    {
        foreach ([
            app_path(),
            base_path('src'),
            base_path('routes'),
        ] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                $path = $file->getPathname();

                if ($path === app_path('Console/Commands/AuditLegacyFallbackCoverage.php')) {
                    continue;
                }

                $contents = File::get($path);

                $this->assertStringNotContainsString(
                    'env(',
                    $contents,
                    "{$path} should read Laravel config instead of env() directly."
                );
            }
        }
    }

    public function test_white_label_database_bootstrap_uses_explicit_request_host_boundary(): void
    {
        $provider = File::get(app_path('Providers/DBWhiteLabelProvider.php'));
        $service = File::get(app_path('Services/DBWhiteLabelService.php'));

        $this->assertStringContainsString('Illuminate\\Http\\Request', $provider);
        $this->assertStringContainsString('$request->getHttpHost()', $provider);
        $this->assertStringNotContainsString('request()->getHttpHost()', $provider);
        $this->assertStringNotContainsString('request()->getHttpHost()', $service);
        $this->assertStringContainsString('getSubDomain($url)', $service);
    }

    private function assertRouteAction(string $uri, string $method, string $expectedAction): void
    {
        $this->assertSame(
            $expectedAction,
            Route::getRoutes()->match(Request::create($uri, $method))->getActionName()
        );
    }
}
