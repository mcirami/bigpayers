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

    public function test_public_auth_flows_use_legacy_auth_boundaries(): void
    {
        $loginController = File::get(app_path('Http/Controllers/LegacyLoginController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyLogin as Login', $loginController);
        $this->assertStringContainsString('App\\Support\\LegacyUser as User', $loginController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Login', $loginController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $loginController);

        $signupController = File::get(app_path('Http/Controllers/SignupController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyAffiliateSignUp as AffiliateSignUp', $signupController);
        $this->assertStringContainsString('App\\Support\\LegacyUser as User', $signupController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\AffiliateSignUp', $signupController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $signupController);

        foreach ([
            app_path('Http/Controllers/LegacyCompatibilityController.php'),
            app_path('Http/Middleware/LegacyUserAuth.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyUser as User', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\Login',
            File::get(app_path('Support/LegacyLogin.php'))
        );
        $login = File::get(base_path('src/User/Login.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $login);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $login);
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\AffiliateSignUp',
            File::get(app_path('Support/LegacyAffiliateSignUp.php'))
        );
        $this->assertStringContainsString(
            'App\\Support\\LegacyConnection as Connection',
            File::get(base_path('src/User/AffiliateSignUp.php'))
        );
        $this->assertStringContainsString(
            'App\\Support\\LegacyDatabaseConnection as DatabaseConnection',
            File::get(base_path('src/User/AffiliateSignUp.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\System\\Connection',
            File::get(base_path('src/User/AffiliateSignUp.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Database\\DatabaseConnection',
            File::get(base_path('src/User/AffiliateSignUp.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\User',
            File::get(app_path('Support/LegacyUser.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Connection',
            File::get(app_path('Support/LegacyConnection.php'))
        );
    }

    public function test_modern_user_reads_use_legacy_user_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/BonusController.php'),
            app_path('Http/Controllers/OfferController.php'),
            app_path('Http/Controllers/UserController.php'),
            base_path('src/Clicks/ClickVars.php'),
            base_path('src/Clicks/Conversion.php'),
            base_path('src/Clicks/URLEvents/URLEvent.php'),
            base_path('src/Database/Versions/V158.php'),
            base_path('src/Offer/Create.php'),
            base_path('src/Offer/RepHasOffer.php'),
            base_path('src/Offer/SaleLog.php'),
            base_path('src/Offer/Update.php'),
            base_path('src/Offer/View.php'),
            base_path('src/System/Session.php'),
            base_path('src/User/PostBackURLs/ConversionPostBackURL.php'),
            base_path('src/User/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyUser', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\User', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\User',
            File::get(app_path('Support/LegacyUser.php'))
        );
        $legacyUser = File::get(base_path('src/User/User.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $legacyUser);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $legacyUser);
    }

    public function test_modern_user_domain_helpers_use_legacy_boundaries(): void
    {
        $bonusController = File::get(app_path('Http/Controllers/BonusController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyBonus', $bonusController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Bonus', $bonusController);

        foreach ([
            base_path('src/Clicks/Conversion.php'),
            base_path('src/Clicks/URLEvents/BonusRegistrationEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyBonus as Bonus', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Bonus', $contents);
        }

        $salaryController = File::get(app_path('Http/Controllers/SalaryController.php'));

        $this->assertStringContainsString('App\\Support\\LegacySalary', $salaryController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Salary', $salaryController);

        $globalPostbackController = File::get(app_path('Http/Controllers/GlobalPostbackController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyPostBackUrl as PostBackUrl', $globalPostbackController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackUrl', $globalPostbackController);

        $userController = File::get(app_path('Http/Controllers/UserController.php'));

        foreach ([
            'App\\Support\\LegacyBonus as Bonus',
            'App\\Support\\LegacyPrivileges as Privileges',
            'App\\Support\\LegacyReferrals as Referrals',
            'App\\Support\\LegacyReportPermissions as ReportPermissions',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $userController);
        }

        foreach ([
            base_path('src/Clicks/Conversion.php') => 'App\\Support\\LegacyReferrals as Referrals',
            base_path('src/Database/Versions/V148.php') => 'App\\Support\\LegacyReportPermissions as ReportPermissions',
            base_path('src/Offer/Deduction.php') => 'App\\Support\\LegacyReferrals as Referrals',
            base_path('src/Offer/Offer.php') => 'App\\Support\\LegacyPrivileges as Privileges',
            base_path('src/Report/Affiliate.php') => 'App\\Support\\LegacyReportPermissions as ReportPermissions',
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
            'LegacyBonus.php' => 'LeadMax\\TrackYourStats\\User\\Bonus',
            'LegacySalary.php' => 'LeadMax\\TrackYourStats\\User\\Salary',
            'LegacyPostBackUrl.php' => 'LeadMax\\TrackYourStats\\User\\PostBackUrl',
            'LegacyPrivileges.php' => 'LeadMax\\TrackYourStats\\User\\Privileges',
            'LegacyReferrals.php' => 'LeadMax\\TrackYourStats\\User\\Referrals',
            'LegacyReportPermissions.php' => 'LeadMax\\TrackYourStats\\User\\ReportPermissions',
        ] as $wrapper => $legacyClass) {
            $this->assertStringContainsString(
                $legacyClass,
                File::get(app_path("Support/{$wrapper}"))
            );
        }

        foreach ([
            base_path('src/User/BanUser.php'),
            base_path('src/User/Bonus.php'),
            base_path('src/User/Create.php'),
            base_path('src/User/CreateUser.php'),
            base_path('src/User/Permissions.php'),
            base_path('src/User/PostBackUrl.php'),
            base_path('src/User/Privileges.php'),
            base_path('src/User/Referrals.php'),
            base_path('src/User/ReportPermissions.php'),
            base_path('src/User/Salary.php'),
            base_path('src/User/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }

        $deduction = File::get(base_path('src/Offer/Deduction.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $deduction);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $deduction);
    }

    public function test_modern_offer_domain_helpers_use_legacy_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/OfferController.php'),
            base_path('src/Clicks/ClickVars.php'),
            base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'),
            base_path('src/Clicks/URLEvents/ConversionRegistrationEvent.php'),
            base_path('src/Clicks/URLEvents/URLEvent.php'),
            base_path('src/Database/Versions/V158.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyOffer', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Offer', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\RepHasOffer', $contents);
        }

        foreach ([
            app_path('Http/Controllers/AffiliateMassPostbackController.php'),
            app_path('Http/Controllers/OfferController.php'),
            base_path('src/Clicks/ClickVars.php'),
            base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'),
            base_path('src/Database/Versions/V158.php'),
            base_path('src/User/Create.php'),
            base_path('src/User/CreateUser.php'),
            base_path('src/User/PostBackURLs/ConversionPostBackURL.php'),
            base_path('src/User/User.php'),
        ] as $path) {
            $this->assertStringContainsString('App\\Support\\LegacyRepHasOffer', File::get($path));
        }

        $userController = File::get(app_path('Http/Controllers/UserController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyRepHasOffer as RepHasOffer', $userController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\RepHasOffer', $userController);

        $offerCreateView = File::get(resource_path('views/offer/create.blade.php'));

        $this->assertStringContainsString('App\\Support\\LegacyOffer', $offerCreateView);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Offer', $offerCreateView);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Offer',
            File::get(app_path('Support/LegacyOffer.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\RepHasOffer',
            File::get(app_path('Support/LegacyRepHasOffer.php'))
        );

        $offer = File::get(base_path('src/Offer/Offer.php'));
        $repHasOffer = File::get(base_path('src/Offer/RepHasOffer.php'));
        $offerUpdate = File::get(base_path('src/Offer/Update.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $offer);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $offer);
        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $repHasOffer);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $repHasOffer);
        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $offerUpdate);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $offerUpdate);
    }

    public function test_modern_offer_postback_urls_use_legacy_boundaries(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));

        foreach ([
            'App\\Support\\LegacyConversionPostBackURL',
            'App\\Support\\LegacyFreePostBackURL',
            'App\\Support\\LegacyDeductionPostBackURL',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $offerController);
        }

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackURLs', $offerController);

        foreach ([
            base_path('src/Clicks/URLEvents/ConversionRegistrationEvent.php') => 'App\\Support\\LegacyConversionPostBackURL as ConversionPostBackURL',
            base_path('src/Clicks/URLEvents/DeductionRegistrationEvent.php') => 'App\\Support\\LegacyDeductionPostBackURL as DeductionPostBackURL',
            base_path('src/Clicks/URLEvents/FreeSignUpRegistrationEvent.php') => 'App\\Support\\LegacyFreePostBackURL as FreePostBackURL',
        ] as $path => $expectedImport) {
            $contents = File::get($path);

            $this->assertStringContainsString($expectedImport, $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\PostBackURLs', $contents);
        }

        foreach ([
            'LegacyConversionPostBackURL.php' => 'LeadMax\\TrackYourStats\\User\\PostBackURLs\\ConversionPostBackURL',
            'LegacyFreePostBackURL.php' => 'LeadMax\\TrackYourStats\\User\\PostBackURLs\\FreePostBackURL',
            'LegacyDeductionPostBackURL.php' => 'LeadMax\\TrackYourStats\\User\\PostBackURLs\\DeductionPostBackURL',
        ] as $wrapper => $legacyClass) {
            $this->assertStringContainsString(
                $legacyClass,
                File::get(app_path("Support/{$wrapper}"))
            );
        }

        foreach ([
            base_path('src/User/PostBackURLs/ConversionPostBackURL.php'),
            base_path('src/User/PostBackURLs/FreePostBackURL.php'),
            base_path('src/User/PostBackURLs/DeductionPostBackURL.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_modern_offer_support_helpers_use_legacy_boundaries(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $legacySeedVersion = File::get(base_path('src/Database/Versions/V158.php'));

        $this->assertStringContainsString('App\\Support\\LegacyCampaigns as Campaigns', $offerController);
        $this->assertStringContainsString('App\\Support\\LegacyCampaigns as Campaigns', $legacySeedVersion);
        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $legacySeedVersion);
        $this->assertStringContainsString(
            'App\\Support\\LegacyCaps as Caps',
            File::get(base_path('src/Offer/Rules/Device.php'))
        );
        $this->assertStringContainsString('App\\Support\\LegacyCreateOffer as CreateOffer', $legacySeedVersion);
        $this->assertStringContainsString(
            'App\\Support\\LegacyFreeSignUp as FreeSignUp',
            File::get(base_path('src/Clicks/URLEvents/FreeSignUpRegistrationEvent.php'))
        );
        $this->assertStringContainsString('App\\Support\\LegacyOfferView', $offerController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Campaigns', $offerController);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Campaigns', $legacySeedVersion);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $legacySeedVersion);
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Caps',
            File::get(base_path('src/Offer/Rules/Device.php'))
        );
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\CreateOffer', $legacySeedVersion);
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Offer\\FreeSignUp',
            File::get(base_path('src/Clicks/URLEvents/FreeSignUpRegistrationEvent.php'))
        );
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\View', $offerController);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Caps',
            File::get(app_path('Support/LegacyCaps.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\Campaigns',
            File::get(app_path('Support/LegacyCampaigns.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\CreateOffer',
            File::get(app_path('Support/LegacyCreateOffer.php'))
        );
        $this->assertStringContainsString(
            'App\\Support\\LegacyDatabaseConnection as DatabaseConnection',
            File::get(base_path('src/Offer/CreateOffer.php'))
        );
        $this->assertStringNotContainsString(
            'LeadMax\\TrackYourStats\\Database\\DatabaseConnection',
            File::get(base_path('src/Offer/CreateOffer.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\FreeSignUp',
            File::get(app_path('Support/LegacyFreeSignUp.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\View',
            File::get(app_path('Support/LegacyOfferView.php'))
        );

        $freeSignUp = File::get(base_path('src/Offer/FreeSignUp.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $freeSignUp);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $freeSignUp);

        foreach ([
            base_path('src/Offer/Caps.php'),
            base_path('src/Offer/Campaigns.php'),
            base_path('src/Offer/View.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_modern_offer_rule_helpers_use_legacy_boundaries(): void
    {
        $offerController = File::get(app_path('Http/Controllers/OfferController.php'));
        $clickRegistrationEvent = File::get(base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'));

        foreach ([
            'App\\Support\\LegacyDeviceRuleHandler',
            'App\\Support\\LegacyGeoRuleHandler',
            'App\\Support\\LegacyNoneUniqueRuleHandler',
            'App\\Support\\LegacyOfferRuleGeo',
            'App\\Support\\LegacyOfferRules',
        ] as $expectedImport) {
            $this->assertStringContainsString($expectedImport, $offerController);
        }

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Rules', $offerController);
        $this->assertStringContainsString('App\\Support\\LegacyOfferRules as Rules', $clickRegistrationEvent);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\Rules', $clickRegistrationEvent);

        foreach ([
            'LegacyOfferRules.php' => 'LeadMax\\TrackYourStats\\Offer\\Rules',
            'LegacyOfferRuleGeo.php' => 'LeadMax\\TrackYourStats\\Offer\\Rules\\Geo',
            'LegacyGeoRuleHandler.php' => 'LeadMax\\TrackYourStats\\Offer\\Rules\\Handlers\\Geo',
            'LegacyDeviceRuleHandler.php' => 'LeadMax\\TrackYourStats\\Offer\\Rules\\Handlers\\Device',
            'LegacyNoneUniqueRuleHandler.php' => 'LeadMax\\TrackYourStats\\Offer\\Rules\\Handlers\\NoneUnique',
        ] as $wrapper => $legacyClass) {
            $this->assertStringContainsString(
                $legacyClass,
                File::get(app_path("Support/{$wrapper}"))
            );
        }

        foreach ([
            base_path('src/Offer/Rules.php'),
            base_path('src/Offer/Rules/Handlers/Device.php'),
            base_path('src/Offer/Rules/Handlers/Geo.php'),
            base_path('src/Offer/Rules/Handlers/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
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

    public function test_modern_database_updates_use_legacy_company_updater_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/DatabaseUpdateController.php'));
        $companyUpdater = File::get(base_path('src/Database/CompanyUpdater.php'));

        $this->assertStringContainsString('App\\Support\\LegacyCompanyUpdater as CompanyUpdater', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\CompanyUpdater', $controller);
        $this->assertStringContainsString('App\\Support\\LegacyConnection as Connection', $companyUpdater);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Connection', $companyUpdater);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Database\\CompanyUpdater',
            File::get(app_path('Support/LegacyCompanyUpdater.php'))
        );
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

        $urls = File::get(base_path('src/Offer/URLs.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $urls);
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
            base_path('src/User/PasswordReset.php'),
            base_path('src/User/User.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyMail as Mail', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Mail', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Mail',
            File::get(app_path('Support/LegacyMail.php'))
        );
        $passwordReset = File::get(base_path('src/User/PasswordReset.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $passwordReset);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $passwordReset);
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

    public function test_modern_click_writes_use_legacy_click_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyClick as Click', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\Click;', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\Click',
            File::get(app_path('Support/LegacyClick.php'))
        );

        $click = File::get(base_path('src/Clicks/Click.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $click);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $click);
    }

    public function test_modern_click_vars_reads_use_legacy_click_vars_boundary(): void
    {
        foreach ([
            base_path('src/Clicks/URLEvents/URLEvent.php'),
            base_path('src/Database/Versions/V130.php'),
            base_path('src/Database/Versions/V164.php'),
            base_path('src/Report/ID/Clicks.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyClickVars as ClickVars', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickVars', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\ClickVars',
            File::get(app_path('Support/LegacyClickVars.php'))
        );
        $clickVars = File::get(base_path('src/Clicks/ClickVars.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $clickVars);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $clickVars);
    }

    public function test_modern_click_search_reads_use_legacy_click_searcher_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/ClickSearchController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyClickSearcher as ClickSearcher', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\ClickSearcher', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\ClickSearcher',
            File::get(app_path('Support/LegacyClickSearcher.php'))
        );
        $clickSearcher = File::get(base_path('src/Clicks/ClickSearcher.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $clickSearcher);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $clickSearcher);
    }

    public function test_modern_conversion_reads_use_legacy_conversion_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/ChatLogController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'),
            base_path('src/Clicks/URLEvents/ConversionRegistrationEvent.php'),
            base_path('src/Clicks/URLEvents/DeductionRegistrationEvent.php'),
            base_path('src/Offer/SaleLog.php'),
            base_path('src/User/ReferralRegister.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyConversion as Conversion', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\Conversion', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\Conversion',
            File::get(app_path('Support/LegacyConversion.php'))
        );

        $conversion = File::get(base_path('src/Clicks/Conversion.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $conversion);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $conversion);

        $referralRegister = File::get(base_path('src/User/ReferralRegister.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $referralRegister);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $referralRegister);
    }

    public function test_modern_pending_conversion_reads_use_legacy_pending_conversion_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/ChatLogController.php'),
            base_path('src/Clicks/URLEvents/ConversionRegistrationEvent.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyPendingConversion as PendingConversion', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\PendingConversion', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\PendingConversion',
            File::get(app_path('Support/LegacyPendingConversion.php'))
        );

        $pendingConversion = File::get(base_path('src/Clicks/PendingConversion.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $pendingConversion);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $pendingConversion);
    }

    public function test_modern_click_id_reads_use_legacy_uid_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/ClickIdToolController.php'),
            app_path('Http/Controllers/ClickSearchController.php'),
            base_path('src/Clicks/URLTagReplacers/TYSVariables.php'),
            base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'),
            base_path('src/Clicks/URLEvents/Listeners/ConversionListener.php'),
            base_path('src/Clicks/URLEvents/Listeners/DeductionListener.php'),
            base_path('src/Clicks/URLEvents/Listeners/FreeSignUpListener.php'),
            base_path('src/Clicks/URLEvents/URLEvent.php'),
            base_path('src/Database/Stubs/ConversionRegister.php'),
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
            base_path('src/Clicks/Conversion.php'),
            base_path('src/Report/Employee.php'),
            base_path('src/Report/ID/Clicks.php'),
            base_path('src/Report/Offer.php'),
            base_path('src/Report/Repositories/Employee/AdminEmployeeRepository.php'),
            base_path('src/Report/Repositories/Employee/GodEmployeeRepository.php'),
            base_path('src/Report/Repositories/Employee/ManagerEmployeeRepository.php'),
            base_path('src/Report/Repositories/Offer/AdminOfferRepository.php'),
            base_path('src/Report/Repositories/Offer/GodOfferRepository.php'),
            base_path('src/Report/Repositories/Offer/ManagerOfferRepository.php'),
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

    public function test_modern_adjustment_log_reads_use_legacy_adjustments_log_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/AdjustmentsController.php'),
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            base_path('src/Report/Repositories/AdjustmentsLogRepository.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyAdjustmentsLog as AdjustmentsLog', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\AdjustmentsLog',
            File::get(app_path('Support/LegacyAdjustmentsLog.php'))
        );

        $adjustmentsLog = File::get(base_path('src/Offer/AdjustmentsLog.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $adjustmentsLog);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $adjustmentsLog);
    }

    public function test_modern_sale_log_reads_use_legacy_sale_log_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/ChatLogController.php'));

        $this->assertStringContainsString('App\\Support\\LegacySaleLog as SaleLog', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Offer\\SaleLog', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Offer\\SaleLog',
            File::get(app_path('Support/LegacySaleLog.php'))
        );

        $saleLog = File::get(base_path('src/Offer/SaleLog.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $saleLog);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $saleLog);
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
            base_path('src/Offer/View.php'),
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

    public function test_modern_assignments_reads_use_legacy_assignments_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/ClickReportController.php'),
            base_path('src/Offer/Create.php'),
            base_path('src/Offer/Update.php'),
            base_path('src/Report/Filters/ClickLink.php'),
            base_path('src/User/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyAssignments as Assignments', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Table\\Assignments', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Table\\Assignments',
            File::get(app_path('Support/LegacyAssignments.php'))
        );
    }

    public function test_modern_tree_reads_use_legacy_tree_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/UserController.php'),
            app_path('Observers/UserObserver.php'),
            base_path('src/Offer/RepHasOffer.php'),
            base_path('src/Offer/Update.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyTree as Tree', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\Tree', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\Tree',
            File::get(app_path('Support/LegacyTree.php'))
        );

        $tree = File::get(base_path('src/User/Tree.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $tree);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $tree);
    }

    public function test_modern_admin_login_scripts_use_legacy_admin_login_boundary(): void
    {
        foreach ([
            resource_path('views/layouts/footer.blade.php'),
            resource_path('views/layouts/partials/report-script-assets.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyAdminLogin', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\User\\AdminLogin', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\User\\AdminLogin',
            File::get(app_path('Support/LegacyAdminLogin.php'))
        );
    }

    public function test_modern_notification_layouts_use_legacy_notify_boundary(): void
    {
        $layout = File::get(resource_path('views/layouts/master.blade.php'));

        $this->assertStringContainsString('App\\Support\\LegacyNotify::info', $layout);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Notify', $layout);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Notify',
            File::get(app_path('Support/LegacyNotify.php'))
        );
    }

    public function test_modern_report_views_use_legacy_report_html_boundary(): void
    {
        foreach ([
            resource_path('views/report/adjustments.blade.php'),
            resource_path('views/report/advertiser.blade.php'),
            resource_path('views/report/chat-log-affiliate.blade.php'),
            resource_path('views/report/chat-log.blade.php'),
            resource_path('views/report/employee.blade.php'),
            resource_path('views/report/offer/admin.blade.php'),
            resource_path('views/report/offer/affiliate.blade.php'),
            resource_path('views/report/sub.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyReportHtml', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Formats\\HTML', $contents);
        }

        $aggregateController = File::get(app_path('Http/Controllers/Report/AggregateReportController.php'));
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Formats\\HTML', $aggregateController);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Report\\Formats\\HTML',
            File::get(app_path('Support/LegacyReportHtml.php'))
        );
    }

    public function test_modern_click_offer_reports_use_legacy_report_id_offer_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/Report/ClickReportController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyReportIdOffer', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\ID\\Offer', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Report\\ID\\Offer',
            File::get(app_path('Support/LegacyReportIdOffer.php'))
        );
    }

    public function test_modern_report_controllers_use_legacy_reporter_boundary(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/AdvertiserReportController.php'),
            app_path('Http/Controllers/Report/AggregateReportController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyReporter as Reporter', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Reporter', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Report\\Reporter',
            File::get(app_path('Support/LegacyReporter.php'))
        );
    }

    public function test_modern_report_controllers_use_legacy_report_filter_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/AdvertiserReportController.php'),
            app_path('Http/Controllers/Report/AggregateReportController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
            base_path('src/Report/AffiliatePayout.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
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
            $this->assertStringContainsString(
                'LeadMax\\TrackYourStats\\Report\\Filters',
                File::get($path)
            );
        }
    }

    public function test_modern_report_controllers_use_legacy_report_object_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/BlackListReportController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            base_path('src/Report/BlackList.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Affiliate;', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\AffiliatePayout', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\BlackList', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\BlackListRepository', $contents);
        }

        foreach ([
            app_path('Support/LegacyAffiliateReport.php') => 'LeadMax\\TrackYourStats\\Report\\Affiliate',
            app_path('Support/LegacyAffiliatePayoutReport.php') => 'LeadMax\\TrackYourStats\\Report\\AffiliatePayout',
            app_path('Support/LegacyBlackListReport.php') => 'LeadMax\\TrackYourStats\\Report\\BlackList',
            app_path('Support/LegacyBlackListRepository.php') => 'LeadMax\\TrackYourStats\\Report\\Repositories\\BlackListRepository',
        ] as $path => $legacyClass) {
            $this->assertStringContainsString($legacyClass, File::get($path));
        }

        $blackListRepository = File::get(base_path('src/Report/Repositories/BlackListRepository.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $blackListRepository);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $blackListRepository);
    }

    public function test_modern_report_controllers_use_legacy_database_connection_boundary(): void
    {
        $controller = File::get(app_path('Http/Controllers/Report/OfferReportController.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $controller);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $controller);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Database\\DatabaseConnection',
            File::get(app_path('Support/LegacyDatabaseConnection.php'))
        );

        foreach ([
            base_path('src/Report/Affiliate.php'),
            base_path('src/Report/AffiliatePayout.php'),
            base_path('src/Report/Employee.php'),
            base_path('src/Report/ID/Clicks.php'),
            base_path('src/Report/Offer.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_legacy_system_helpers_use_legacy_database_connection_boundary(): void
    {
        foreach ([
            base_path('src/System/Company.php'),
            base_path('src/System/Connection.php'),
            base_path('src/System/DBUpdater.php'),
            base_path('src/System/Database.php'),
            base_path('src/System/Functions.php'),
            base_path('src/System/Log.php'),
            base_path('src/System/Setup.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $contents);
        }
    }

    public function test_modern_offer_report_repositories_use_legacy_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/ExportDataController.php'),
            app_path('Http/Controllers/Report/OfferReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\Offer', $contents);
        }

        foreach ([
            app_path('Support/LegacyAdminOfferRepository.php'),
            app_path('Support/LegacyAffiliateOfferRepository.php'),
            app_path('Support/LegacyGodOfferRepository.php'),
            app_path('Support/LegacyManagerOfferRepository.php'),
        ] as $path) {
            $this->assertStringContainsString(
                'LeadMax\\TrackYourStats\\Report\\Repositories\\Offer',
                File::get($path)
            );
        }
    }

    public function test_modern_employee_report_repositories_use_legacy_boundaries(): void
    {
        foreach ([
            app_path('Console/Commands/AggregateReportData.php'),
            app_path('Console/Commands/PayoutLogsRun.php'),
            app_path('Http/Controllers/ExportDataController.php'),
            app_path('Http/Controllers/Report/EmployeeReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
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
            $this->assertStringContainsString(
                'LeadMax\\TrackYourStats\\Report\\Repositories\\Employee',
                File::get($path)
            );
        }
    }

    public function test_modern_misc_report_repositories_use_legacy_boundaries(): void
    {
        foreach ([
            app_path('Http/Controllers/Report/AdjustmentsReportController.php'),
            app_path('Http/Controllers/Report/AdvertiserReportController.php'),
            app_path('Http/Controllers/Report/AggregateReportController.php'),
            app_path('Http/Controllers/Report/ChatLogReportController.php'),
            app_path('Http/Controllers/Report/PayoutReportController.php'),
            app_path('Http/Controllers/Report/SubReportController.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AdjustmentsLogRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AdvertiserRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AffiliateChatLogRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\AggregateReportRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\PayoutLogRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\SaleLogRepository', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Report\\Repositories\\SubVarRepository', $contents);
        }

        foreach ([
            app_path('Support/LegacyAdjustmentsLogRepository.php'),
            app_path('Support/LegacyAdvertiserRepository.php'),
            app_path('Support/LegacyAffiliateChatLogRepository.php'),
            app_path('Support/LegacyAggregateReportRepository.php'),
            app_path('Support/LegacyPayoutLogRepository.php'),
            app_path('Support/LegacySaleLogRepository.php'),
            app_path('Support/LegacySubVarRepository.php'),
        ] as $path) {
            $this->assertStringContainsString(
                'LeadMax\\TrackYourStats\\Report\\Repositories',
                File::get($path)
            );
        }
        $referralRepository = File::get(base_path('src/Report/Repositories/ReferralRepository.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $referralRepository);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $referralRepository);
    }

    public function test_modern_tracking_parameter_reads_use_legacy_tracking_parameters_boundary(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            base_path('src/Clicks/URLEvents/Listeners/BonusListener.php'),
            base_path('src/Clicks/URLEvents/Listeners/ClickListener.php'),
            base_path('src/Clicks/URLEvents/Listeners/Listener.php'),
            base_path('src/Offer/Caps.php'),
            base_path('src/Offer/Rules.php'),
            base_path('src/Offer/Rules/NoneUnique.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyTrackingParameters as TrackingParameters', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\TrackingParameters', $contents);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\TrackingParameters',
            File::get(app_path('Support/LegacyTrackingParameters.php'))
        );
    }

    public function test_index_click_registration_uses_legacy_event_boundaries(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            base_path('src/Clicks/URLEvents/Listeners/ClickListener.php'),
        ] as $path) {
            $controller = File::get($path);

            if ($path === (new ReflectionClass(IndexController::class))->getFileName()) {
                $this->assertStringContainsString('App\\Support\\LegacyPostBackURLEventHandler as PostBackURLEventHandler', $controller);
                $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler', $controller);
            }

            $this->assertStringContainsString('App\\Support\\LegacyClickRegistrationEvent as ClickRegistrationEvent', $controller);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Clicks\\URLEvents\\ClickRegistrationEvent', $controller);
        }

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\PostBackURLEventHandler',
            File::get(app_path('Support/LegacyPostBackURLEventHandler.php'))
        );
        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\Clicks\\URLEvents\\ClickRegistrationEvent',
            File::get(app_path('Support/LegacyClickRegistrationEvent.php'))
        );

        $clickRegistrationEvent = File::get(base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $clickRegistrationEvent);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $clickRegistrationEvent);
    }

    public function test_modern_ip_blacklist_reads_use_legacy_ip_blacklist_boundary(): void
    {
        foreach ([
            (new ReflectionClass(IndexController::class))->getFileName(),
            (new ReflectionClass(IPBlacklistController::class))->getFileName(),
            base_path('src/Clicks/URLEvents/ClickRegistrationEvent.php'),
            base_path('src/System/IPBlackList.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\Legacy', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\IPBlackList', $contents);
        }

        $ipBlackList = File::get(base_path('src/System/IPBlackList.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $ipBlackList);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $ipBlackList);

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
            base_path('src/Offer/RepHasOffer.php'),
            base_path('src/User/AffiliateSignUp.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringContainsString('App\\Support\\LegacyNotifications as Notifications', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Notifications', $contents);
        }

        $notifications = File::get(base_path('src/System/Notifications.php'));

        $this->assertStringContainsString('App\\Support\\LegacyDatabaseConnection as DatabaseConnection', $notifications);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\Database\\DatabaseConnection', $notifications);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\Notifications',
            File::get(app_path('Support/LegacyNotifications.php'))
        );
    }

    public function test_dashboard_navigation_uses_legacy_navbar_boundary(): void
    {
        $provider = File::get((new ReflectionClass(AppServiceProvider::class))->getFileName());

        $this->assertStringContainsString('App\\Support\\LegacyNavBar as NavBar', $provider);
        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\NavBar', $provider);

        $this->assertStringContainsString(
            'LeadMax\\TrackYourStats\\System\\NavBar',
            File::get(app_path('Support/LegacyNavBar.php'))
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
