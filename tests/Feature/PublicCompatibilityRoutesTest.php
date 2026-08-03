<?php

namespace Tests\Feature;

use App\Company;
use App\Services\SaleLogImageStorage;
use App\Support\NativeSession;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicCompatibilityRoutesTest extends TestCase
{
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
        $originalSubDomain = NativeSession::get('COMPANY_SUBDOMAIN');

        try {
            NativeSession::put('COMPANY_SUBDOMAIN', 'tenant-a');
            $this->assertSame('tenant-a', Company::currentSubDomain());

            NativeSession::forget('COMPANY_SUBDOMAIN');
            $this->assertSame((string) config('database.connections.mysql.database'), Company::currentSubDomain());
        } finally {
            if ($originalSubDomain === null) {
                NativeSession::forget('COMPANY_SUBDOMAIN');
            } else {
                NativeSession::put('COMPANY_SUBDOMAIN', $originalSubDomain);
            }
        }
    }

    public function test_sale_log_image_storage_builds_configured_paths(): void
    {
        $originalSubDomain = NativeSession::get('COMPANY_SUBDOMAIN');

        try {
            config(['filesystems.sale_log_directory' => '/var/bigpayers/sale-logs/']);
            NativeSession::put('COMPANY_SUBDOMAIN', 'tenant-a');

            $this->assertSame('/var/bigpayers/sale-logs', SaleLogImageStorage::root());
            $this->assertSame('/var/bigpayers/sale-logs/tenant-a', SaleLogImageStorage::companyDirectory());
            $this->assertSame('/var/bigpayers/sale-logs/tenant-b', SaleLogImageStorage::companyDirectory('tenant-b'));
            $this->assertSame('/var/bigpayers/sale-logs/tenant-a/42', SaleLogImageStorage::saleLogDirectory(42));
            $this->assertSame('/var/bigpayers/sale-logs/tenant-b/42/image.png', SaleLogImageStorage::saleLogFilePath(42, '../image.png', 'tenant-b'));
        } finally {
            if ($originalSubDomain === null) {
                NativeSession::forget('COMPANY_SUBDOMAIN');
            } else {
                NativeSession::put('COMPANY_SUBDOMAIN', $originalSubDomain);
            }
        }
    }

    public function test_laravel_company_model_uses_native_session_boundary(): void
    {
        $companyModel = File::get(app_path('Company.php'));
        $loginController = File::get(app_path('Http/Controllers/SessionLoginController.php'));

        $this->assertStringContainsString('App\\Support\\NativeSession', $companyModel);
        $this->assertStringContainsString('App\\Support\\NativeSession', $loginController);
        $this->assertStringNotContainsString('$_SESSION', $companyModel);
        $this->assertStringNotContainsString('$_SESSION', $loginController);
        $this->assertStringNotContainsString('$_GET', $loginController);
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

        $signup = File::get(resource_path('views/auth/signup.blade.php'));
        $login = File::get(resource_path('views/auth/login.blade.php'));

        $this->assertStringContainsString('action="/signup"', $signup);
        $this->assertStringNotContainsString("request()->path() === 'signup.php'", $signup);
        $this->assertStringContainsString('$redirectUri', $login);
        $this->assertStringNotContainsString("request()->has('redirectUri')", $login);
        $this->assertStringNotContainsString("request('redirectUri')", $login);
    }

    public function test_signup_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/SignupController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_login_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/SessionLoginController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_forgot_password_controller_does_not_load_legacy_company_from_session(): void
    {
        $controller = File::get(app_path('Http/Controllers/Auth/ForgotPasswordController.php'));

        $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $controller);
        $this->assertStringNotContainsString('Company::loadFromSession()', $controller);
    }

    public function test_dashboard_branding_surfaces_do_not_load_legacy_company_from_session(): void
    {
        foreach ([
            app_path('Http/Controllers/DashboardController.php'),
            resource_path('views/home.blade.php'),
            resource_path('views/layouts/dashboard-shell.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Company', $contents);
            $this->assertStringNotContainsString('Company::loadFromSession()', $contents);
        }
    }

    public function test_error_and_pdf_views_do_not_load_legacy_company_from_session(): void
    {
        foreach ([
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
            resource_path('views/errors/403.blade.php'),
            resource_path('views/errors/404.blade.php'),
            resource_path('views/errors/500.blade.php'),
        ] as $path) {
            $contents = File::get($path);

            $this->assertStringNotContainsString('LeadMax\\TrackYourStats\\System\\Session', $contents);
        }
    }

    public function test_blade_views_do_not_read_legacy_session_directly(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $path = $file->getPathname();
            $contents = File::get($path);

            $this->assertStringNotContainsString(
                'LeadMax\\TrackYourStats\\System\\Session',
                $contents,
                "{$path} reads the legacy session class directly."
            );
        }
    }

    public function test_blade_views_read_configuration_instead_of_env_directly(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $path = $file->getPathname();
            $contents = File::get($path);

            $this->assertStringNotContainsString(
                'env(',
                $contents,
                "{$path} should read Laravel config instead of env() directly."
            );
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
            app_path('Support/ImagesUploader.php'),
            app_path('Support/OfferDomain/SaleLog.php'),
            app_path('Support/UserDomain/User.php'),
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

}
