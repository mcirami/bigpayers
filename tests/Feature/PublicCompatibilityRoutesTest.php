<?php

namespace Tests\Feature;

use App\Company;
use App\Http\Controllers\CompanyCssController;
use App\Http\Controllers\LegacyCompatibilityController;
use App\Http\Controllers\PublicCompatibilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Tests\TestCase;

class PublicCompatibilityRoutesTest extends TestCase
{
    public function test_public_php_compatibility_routes_are_registered(): void
    {
        $this->assertSame(
            LegacyCompatibilityController::class . '@redirectLoginPhp',
            Route::getRoutes()->match(Request::create('/login.php'))->getActionName()
        );
        $this->assertSame(
            LegacyCompatibilityController::class . '@redirectLogoutPhp',
            Route::getRoutes()->match(Request::create('/logout.php'))->getActionName()
        );
        $this->assertSame(
            PublicCompatibilityController::class . '@redirectCompanyCss',
            Route::getRoutes()->match(Request::create('/css/company.php'))->getActionName()
        );
        $this->assertSame(
            PublicCompatibilityController::class . '@redirectLoginTheme',
            Route::getRoutes()->match(Request::create('/login_themes/default/index.php'))->getActionName()
        );
        $this->assertSame(
            CompanyCssController::class,
            Route::getRoutes()->match(Request::create('/css/company.css'))->getActionName()
        );
    }

    public function test_public_php_compatibility_controllers_redirect_to_laravel_routes(): void
    {
        $legacyController = app(LegacyCompatibilityController::class);
        $publicController = app(PublicCompatibilityController::class);

        $this->assertStringEndsWith('/login', $legacyController->redirectLoginPhp()->getTargetUrl());
        $this->assertStringEndsWith('/logout', $legacyController->redirectLogoutPhp()->getTargetUrl());
        $this->assertStringEndsWith('/css/company.css', $publicController->redirectCompanyCss()->getTargetUrl());
        $this->assertStringEndsWith('/login', $publicController->redirectLoginTheme()->getTargetUrl());
    }

    public function test_company_css_sanitizes_theme_colors_before_rendering(): void
    {
        $controller = app(CompanyCssController::class);
        $reflection = new ReflectionClass($controller);
        $hexColor = $reflection->getMethod('hexColor');
        $hexColor->setAccessible(true);

        $this->assertSame('AABBCC', $hexColor->invoke($controller, '#abc'));
        $this->assertSame('12ABEF', $hexColor->invoke($controller, '12-AB-EF'));
        $this->assertSame('000000', $hexColor->invoke($controller, 'not-a-color'));
        $this->assertSame('000000', $hexColor->invoke($controller, null));
    }

    public function test_company_css_normalizes_missing_theme_color_slots(): void
    {
        $controller = app(CompanyCssController::class);
        $reflection = new ReflectionClass($controller);
        $normalizedColors = $reflection->getMethod('normalizedColors');
        $normalizedColors->setAccessible(true);

        $colors = $normalizedColors->invoke($controller, ['#abc']);

        $this->assertCount(11, $colors);
        $this->assertSame('AABBCC', $colors[0]);
        $this->assertSame('000000', $colors[1]);
        $this->assertSame('000000', $colors[10]);
    }

    public function test_company_css_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/CompanyCssController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_laravel_company_model_exposes_auth_view_presentation_helpers(): void
    {
        $company = new Company();
        $company->id = 123;
        $company->shortHand = 'Acme Affiliates';
        $company->subDomain = 'acme';
        $company->colors = '#abc;123456';
        $company->messenger_type = '';
        $company->messenger_username = '';
        $company->skype = 'acme-support';
        $company->email = 'support@example.test';
        $company->uid = 'acme-uid';
        $company->login_url = 'login.example.test';
        $company->landing_page = 'landing.example.test';
        $company->allow_register = true;

        $this->assertSame(['#abc', '123456'], $company->getColors());
        $this->assertSame(123, $company->getID());
        $this->assertSame('Acme Affiliates', $company->getShortHand());
        $this->assertSame('acme', $company->getSubDomain());
        $this->assertSame('images/acme', $company->getImgDir());
        $this->assertSame('/images/acme/logo.png', $company->getBrandAssetUrl('logo.png'));
        $this->assertSame('Telegram', $company->getMessengerType());
        $this->assertSame('acme-support', $company->getMessengerUsername());
        $this->assertSame('acme-support', $company->getSkype());
        $this->assertSame('support@example.test', $company->getEmail());
        $this->assertSame('acme-uid', $company->getUID());
        $this->assertSame('login.example.test', $company->getLoginURL());
        $this->assertSame('landing.example.test', $company->getLandingPage());
        $this->assertTrue($company->allowsRegister());
    }

    public function test_laravel_company_model_resolves_current_subdomain_without_legacy_company_class(): void
    {
        $originalSubDomain = $_SESSION['COMPANY_SUBDOMAIN'] ?? null;

        try {
            $_SESSION['COMPANY_SUBDOMAIN'] = 'tenant-a';
            $this->assertSame('tenant-a', Company::currentSubDomain());

            unset($_SESSION['COMPANY_SUBDOMAIN']);
            $this->assertSame((string) env('DB_DATABASE'), Company::currentSubDomain());
        } finally {
            if ($originalSubDomain === null) {
                unset($_SESSION['COMPANY_SUBDOMAIN']);
            } else {
                $_SESSION['COMPANY_SUBDOMAIN'] = $originalSubDomain;
            }
        }
    }

    public function test_laravel_company_model_resolves_login_theme_css_url(): void
    {
        $themeCssPath = public_path('login_themes/command-center/theme.css');
        $this->assertFileExists($themeCssPath);

        $company = new Company();
        $company->login_theme = 'command-center';

        $this->assertSame('command-center', $company->loginTheme());
        $this->assertSame(
            '/login_themes/command-center/theme.css?v=' . filemtime($themeCssPath),
            $company->themeCssUrl()
        );
    }

    public function test_public_auth_views_expect_laravel_company_model(): void
    {
        foreach ([
            'auth/login.blade.php',
            'auth/forgot-password.blade.php',
            'auth/signup.blade.php',
            'auth/signup-success.blade.php',
        ] as $view) {
            $contents = File::get(resource_path("views/{$view}"));

            $this->assertStringContainsString('@var \App\Company $company', $contents);
            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
        }
    }

    public function test_signup_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/SignupController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_login_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/LegacyLoginController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_forgot_password_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/LegacyCompatibilityController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_dashboard_branding_surfaces_do_not_load_legacy_company_from_session(): void
    {
        foreach ([
            app_path('Http/Controllers/DashboardController.php'),
            resource_path('views/home.blade.php'),
            resource_path('views/layouts/dashboard-shell.blade.php'),
            resource_path('views/layouts/master.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
            $this->assertStringNotContainsString('Company::loadFromSession()', $contents);
        }
    }

    public function test_error_and_pdf_views_do_not_load_legacy_company_from_session(): void
    {
        foreach ([
            resource_path('views/contact.blade.php'),
            resource_path('views/errors/403.blade.php'),
            resource_path('views/errors/404.blade.php'),
            resource_path('views/errors/500.blade.php'),
            resource_path('views/pdf/payout-log.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
            $this->assertStringNotContainsString('Company::loadFromSession()', $contents);
        }
    }

    public function test_dashboard_shell_and_error_views_do_not_read_legacy_session_directly(): void
    {
        foreach ([
            resource_path('views/home.blade.php'),
            resource_path('views/layouts/dashboard-shell.blade.php'),
            resource_path('views/layouts/master.blade.php'),
            resource_path('views/errors/403.blade.php'),
            resource_path('views/errors/404.blade.php'),
            resource_path('views/errors/500.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_user_account_views_do_not_read_legacy_session_directly(): void
    {
        foreach ([
            resource_path('views/user/partials/account-actions.blade.php'),
            resource_path('views/user/form.blade.php'),
            resource_path('views/user/offers.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_chat_log_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/ChatLogController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_legacy_helper_classes_do_not_load_legacy_company_from_session(): void
    {
        foreach ([
            base_path('src/System/Files/ImagesUploader.php'),
            base_path('src/Offer/SaleLog.php'),
            base_path('src/User/User.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
            $this->assertStringNotContainsString('Company::loadFromSession()', $contents);
        }
    }

    public function test_index_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/IndexController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_company_css_renderer_uses_sanitized_hex_values(): void
    {
        $controller = app(CompanyCssController::class);
        $reflection = new ReflectionClass($controller);
        $buildCss = $reflection->getMethod('buildCss');
        $buildCss->setAccessible(true);

        $css = $buildCss->invoke($controller, [
            '111111',
            '222222',
            '333333',
            '444444',
            '555555',
            '666666',
            '777777',
            '888888',
            '999999',
            'AAAAAA',
            'BBBBBB',
        ]);

        $this->assertStringContainsString('background-color: #111111;', $css);
        $this->assertStringContainsString('color: #222222!important;', $css);
        $this->assertStringContainsString('background: #888888 ;', $css);
        $this->assertStringContainsString('background: #BBBBBB;', $css);
        $this->assertStringNotContainsString('<?php', $css);
    }
}
