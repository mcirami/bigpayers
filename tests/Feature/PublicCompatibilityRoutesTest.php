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
        $company->shortHand = 'Acme Affiliates';
        $company->subDomain = 'acme';
        $company->colors = '#abc;123456';
        $company->messenger_type = '';
        $company->messenger_username = '';
        $company->skype = 'acme-support';
        $company->email = 'support@example.test';
        $company->allow_register = true;

        $this->assertSame(['#abc', '123456'], $company->getColors());
        $this->assertSame('Acme Affiliates', $company->getShortHand());
        $this->assertSame('images/acme', $company->getImgDir());
        $this->assertSame('/images/acme/logo.png', $company->getBrandAssetUrl('logo.png'));
        $this->assertSame('Telegram', $company->getMessengerType());
        $this->assertSame('acme-support', $company->getMessengerUsername());
        $this->assertSame('support@example.test', $company->getEmail());
        $this->assertTrue($company->allowsRegister());
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
