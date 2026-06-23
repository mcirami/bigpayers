<?php

namespace Tests\Feature;

use App\Company;
use App\Console\Commands\MigrateAllInstalls;
use App\Console\Commands\MigrateSingleCompany;
use App\Services\BaseInstallSql;
use App\Services\BrandingLabels;
use App\Services\CompanyDatabaseConnectionManager;
use App\Services\GeoIpDatabase;
use App\Services\LegacyDatabaseConfig;
use App\Services\LoginBranding;
use App\Services\RuntimeEnvironment;
use App\Services\SmsApiEndpoint;
use App\Services\SmsPoolConfig;
use App\Services\TenantDatabasePdoFactory;
use App\Support\NativeRequest;
use App\Support\RequestContext;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class MigrationCommandsTest extends TestCase
{
    public function test_company_database_connection_manager_configures_tenant_from_mysql_connection(): void
    {
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'db-host',
            'port' => '3307',
            'database' => 'master_db',
            'username' => 'db-user',
            'password' => 'db-pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        $company = new Company();
        $company->subDomain = 'tenant_a';

        $connectionName = (new CompanyDatabaseConnectionManager())->configure($company);

        $this->assertSame('tenant_a', $connectionName);
        $this->assertSame([
            'driver' => 'mysql',
            'host' => 'db-host',
            'port' => '3307',
            'database' => 'tenant_a',
            'username' => 'db-user',
            'password' => 'db-pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ], Config::get('database.connections.tenant_a'));
    }

    public function test_company_database_connection_manager_can_switch_default_connection(): void
    {
        $originalDefaultConnection = DB::getDefaultConnection();

        try {
            $company = new Company();
            $company->subDomain = 'tenant_b';

            $connectionName = (new CompanyDatabaseConnectionManager())->useAsDefault($company);

            $this->assertSame('tenant_b', $connectionName);
            $this->assertSame('tenant_b', DB::getDefaultConnection());
        } finally {
            DB::setDefaultConnection($originalDefaultConnection);
        }
    }

    public function test_company_database_connection_manager_can_configure_named_connections(): void
    {
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'db-host',
            'port' => '3307',
            'database' => 'master_db',
            'username' => 'db-user',
            'password' => 'db-pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        $connectionName = (new CompanyDatabaseConnectionManager())->configureNamed('importing', 'tenant_import');

        $this->assertSame('importing', $connectionName);
        $this->assertSame('tenant_import', Config::get('database.connections.importing.database'));
        $this->assertSame('db-host', Config::get('database.connections.importing.host'));
    }

    public function test_legacy_database_config_reads_laravel_mysql_and_master_connections(): void
    {
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'tenant-db-host',
            'port' => '3307',
            'database' => 'tenant_master',
            'username' => 'tenant-user',
            'password' => 'tenant-pass',
        ]);
        Config::set('database.connections.master', [
            'driver' => 'mysql',
            'host' => 'master-db-host',
            'port' => '3308',
            'database' => 'master_catalog',
            'username' => 'master-user',
            'password' => 'master-pass',
        ]);

        $this->assertSame('tenant-db-host', LegacyDatabaseConfig::mysql('host'));
        $this->assertSame('tenant-db-host', LegacyDatabaseConfig::mysqlConnection()['host']);
        $this->assertSame('tenant_master', LegacyDatabaseConfig::mysql('database'));
        $this->assertSame('tenant_master', LegacyDatabaseConfig::primaryDatabase());
        $this->assertSame('master-db-host', LegacyDatabaseConfig::master('host'));
        $this->assertSame('master_catalog', LegacyDatabaseConfig::master('database'));
    }

    public function test_legacy_database_config_can_read_database_config_before_laravel_config_is_bootstrapped(): void
    {
        $app = app();
        $config = $app->make('config');

        try {
            $app->offsetUnset('config');

            $this->assertSame(env('DB_HOST', '127.0.0.1'), LegacyDatabaseConfig::mysql('host'));
            $this->assertSame(env('DB_DATABASE', 'forge'), LegacyDatabaseConfig::primaryDatabase());
            $this->assertSame(env('MASTER_DB_HOST', '127.0.0.1'), LegacyDatabaseConfig::master('host'));
            $this->assertSame(env('MASTER_DB_DATABASE', 'forge'), LegacyDatabaseConfig::master('database'));
            $this->assertSame(env('DB_USERNAME', 'forge'), LegacyDatabaseConfig::mysqlConnection()['username']);
        } finally {
            $app->instance('config', $config);
        }
    }

    public function test_runtime_configuration_documentation_lists_configured_environment_boundaries(): void
    {
        $documentation = File::get(base_path('docs/runtime-configuration.md'));

        foreach ([
            'TYS_BASE_INSTALL' => 'provisioning.base_install_sql',
            'SALE_LOG_DIRECTORY' => 'filesystems.sale_log_directory',
            'GEO_IP_DATABASE' => 'services.geo.ip_database',
            'SMS_URL' => 'services.sms.base_url',
            'LOGIN_PAGE_TEXT' => 'branding.login.page_text',
            'FORGOT_PASS_PAGE_BUTTON_TEXT' => 'branding.login.forgot_password_button_text',
            'database.connections.mysql' => 'App\Services\LegacyDatabaseConfig',
            'database.connections.master' => 'App\Services\LegacyDatabaseConfig',
        ] as $environmentName => $configKey) {
            $this->assertStringContainsString($environmentName, $documentation);
            $this->assertStringContainsString($configKey, $documentation);
        }
    }

    public function test_sms_api_endpoint_builds_configured_urls(): void
    {
        Config::set('services.sms.base_url', 'https://sms.example.test/');

        $this->assertSame('https://sms.example.test', SmsApiEndpoint::baseUrl());
        $this->assertSame('https://sms.example.test/worker/create', SmsApiEndpoint::url('/worker/create'));
        $this->assertSame('https://sms.example.test/api/messages/send', SmsApiEndpoint::url('api/messages/send'));
    }

    public function test_geo_ip_database_resolves_configured_and_fallback_paths(): void
    {
        Config::set('services.geo.ip_database', __FILE__);

        $this->assertSame(__FILE__, GeoIpDatabase::configuredPath());
        $this->assertSame(__FILE__, GeoIpDatabase::readablePath(base_path()));

        Config::set('services.geo.ip_database', 'configured-but-missing.mmdb');

        $this->assertSame(
            'resources/GeoIP2-City.mmdb',
            GeoIpDatabase::readablePath(base_path('missing-root'))
        );
        $this->assertSame(
            'configured-but-missing.mmdb',
            GeoIpDatabase::readablePath(base_path('missing-root'), GeoIpDatabase::configuredPath())
        );
    }

    public function test_branding_labels_read_configured_account_and_affiliate_labels(): void
    {
        Config::set('branding.account.singular', 'Advertiser');
        Config::set('branding.account.plural', 'Advertisers');
        Config::set('branding.affiliate.singular', 'Partner');
        Config::set('branding.affiliate.plural', 'Partners');

        $this->assertSame('Advertiser', BrandingLabels::account());
        $this->assertSame('Advertisers', BrandingLabels::accounts());
        $this->assertSame('Partner', BrandingLabels::affiliate());
        $this->assertSame('Partners', BrandingLabels::affiliates());
        $this->assertSame([
            'accountTypeLabel' => 'Advertiser',
            'accountTypeLabelPlural' => 'Advertisers',
            'affiliateTypeLabel' => 'Partner',
            'affiliateTypeLabelPlural' => 'Partners',
        ], BrandingLabels::viewData());
    }

    public function test_login_branding_reads_configured_copy(): void
    {
        Config::set('branding.login.page_text', 'Welcome');
        Config::set('branding.login.button_text', 'Login Now');
        Config::set('branding.login.forgot_password_link_text', 'Need help?');
        Config::set('branding.login.forgot_password_page_text', 'Recover access');
        Config::set('branding.login.forgot_password_button_text', 'Send reset');

        $this->assertSame('Welcome', LoginBranding::pageText());
        $this->assertSame('Login Now', LoginBranding::buttonText());
        $this->assertSame('Need help?', LoginBranding::forgotPasswordLinkText());
        $this->assertSame('Recover access', LoginBranding::forgotPasswordPageText());
        $this->assertSame('Send reset', LoginBranding::forgotPasswordButtonText());
        $this->assertSame('Back to login', LoginBranding::returnToLoginText());

        Config::set('branding.login.button_text', 'Sign in');

        $this->assertSame('Return to login', LoginBranding::returnToLoginText());
    }

    public function test_runtime_environment_detects_production_snippet_mode(): void
    {
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $this->assertTrue(RuntimeEnvironment::runsProductionSnippets());

        Config::set('app.debug', true);

        $this->assertFalse(RuntimeEnvironment::runsProductionSnippets());

        Config::set('app.debug', false);
        Config::set('app.env', 'local');

        $this->assertFalse(RuntimeEnvironment::runsProductionSnippets());
    }

    public function test_sms_pool_config_builds_configured_urls(): void
    {
        Config::set('services.smspool.base_url', 'https://pool.example.test/');
        Config::set('services.smspool.key', 'secret-key');

        $this->assertSame('https://pool.example.test', SmsPoolConfig::baseUrl());
        $this->assertSame('secret-key', SmsPoolConfig::apiKey());
        $this->assertSame('https://pool.example.test/purchase/sms', SmsPoolConfig::url('/purchase/sms'));
        $this->assertSame('https://pool.example.test/sms/check', SmsPoolConfig::url('sms/check'));
    }

    public function test_native_request_resolves_client_ip_from_forwarded_headers(): void
    {
        $originalServer = $_SERVER;

        try {
            unset($_SERVER['HTTP_CLIENT_IP'], $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);

            $this->assertSame('fallback-ip', NativeRequest::clientIp('fallback-ip'));
            $this->assertSame('fallback-remote', NativeRequest::remoteAddress('fallback-remote'));

            $_SERVER['REMOTE_ADDR'] = '192.0.2.30';
            $this->assertSame('192.0.2.30', NativeRequest::remoteAddress());
            $this->assertSame('192.0.2.30', NativeRequest::clientIp());

            $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.8, 198.51.100.9';
            $this->assertSame('198.51.100.8', NativeRequest::clientIp());

            $_SERVER['HTTP_CLIENT_IP'] = '203.0.113.15, 203.0.113.16';
            $this->assertSame('203.0.113.15', NativeRequest::clientIp());

            $_SERVER['HTTP_HOST'] = 'app.example.test';
            $_SERVER['REQUEST_URI'] = '/reports?role=3';
            $_SERVER['HTTP_REFERER'] = 'https://referrer.example.test/source';
            $_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
            $_SERVER['SERVER_PORT'] = '443';
            $_SERVER['PHP_SELF'] = '/offer/manage';

            $this->assertSame('app.example.test', NativeRequest::host());
            $this->assertSame('/reports?role=3', NativeRequest::requestUri());
            $this->assertSame('http://app.example.test/reports?role=3', NativeRequest::currentUrl());
            $this->assertSame('https://referrer.example.test/source', NativeRequest::referrer());
            $this->assertSame('Test Browser', NativeRequest::userAgent());
            $this->assertSame('443', NativeRequest::serverPort());
            $this->assertSame('/offer/manage', NativeRequest::scriptName());
        } finally {
            $_SERVER = $originalServer;
        }
    }

    public function test_request_context_resolves_modern_request_host_and_ip_values(): void
    {
        $request = Request::create('https://app.example.test/reports?role=3', 'GET', [], [], [], [
            'HTTP_HOST' => 'app.example.test',
            'SERVER_ADDR' => '192.0.2.20',
            'REMOTE_ADDR' => '192.0.2.30',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.8, 198.51.100.9',
        ]);

        $this->assertSame('https://app.example.test', RequestContext::schemeAndHttpHost($request));
        $this->assertSame('app.example.test', RequestContext::httpHost($request));
        $this->assertSame('app.example.test', RequestContext::host($request));
        $this->assertSame('reports', RequestContext::path($request));
        $this->assertTrue(RequestContext::hasQuery('role', $request));
        $this->assertSame('3', RequestContext::query('role', null, $request));
        $this->assertSame('192.0.2.20', RequestContext::serverAddress($request));
        $this->assertSame('198.51.100.8', RequestContext::clientIp($request));

        $requestWithoutServerAddress = Request::create('http://tenant.example.test', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.15',
        ]);

        $this->assertSame('203.0.113.15', RequestContext::serverAddress($requestWithoutServerAddress));
        $this->assertSame('203.0.113.15', RequestContext::clientIp($requestWithoutServerAddress));
    }

    public function test_migration_commands_use_the_company_database_connection_manager(): void
    {
        $commands = [
            File::get(base_path('app/Console/Commands/AggregateReportData.php')),
            File::get(base_path('app/Console/Commands/MigrateAllInstalls.php')),
            File::get(base_path('app/Console/Commands/MigrateLegacyDatabase.php')),
            File::get(base_path('app/Console/Commands/MigrateSingleCompany.php')),
            File::get(base_path('app/Console/Commands/PayoutLogsRun.php')),
            File::get(base_path('app/Http/Controllers/RelevanceReactorController.php')),
        ];

        foreach ($commands as $commandSource) {
            $this->assertStringContainsString(CompanyDatabaseConnectionManager::class, $commandSource);
            $this->assertStringNotContainsString("env('DB_HOST')", $commandSource);
            $this->assertStringNotContainsString("'--force' => '--force'", $commandSource);
        }

        $this->assertStringContainsString(
            '$this->connections->configure($company)',
            File::get(base_path('app/Console/Commands/MigrateAllInstalls.php'))
        );
        $this->assertStringContainsString(
            '$this->connections->configure($company)',
            File::get(base_path('app/Console/Commands/MigrateSingleCompany.php'))
        );
        $this->assertStringContainsString(
            "\$this->connections->configureNamed('importing', \$database)",
            File::get(base_path('app/Console/Commands/MigrateLegacyDatabase.php'))
        );
        $this->assertStringContainsString(
            '$this->connections->useAsDefault($company)',
            File::get(base_path('app/Console/Commands/AggregateReportData.php'))
        );
        $this->assertStringContainsString(
            '$this->connections->useAsDefault($company)',
            File::get(base_path('app/Console/Commands/PayoutLogsRun.php'))
        );
        $this->assertStringContainsString(
            '?CompanyDatabaseConnectionManager $connections = null',
            File::get(base_path('app/Services/DBWhiteLabelService.php'))
        );
        $this->assertStringContainsString(
            "\$this->connections->configureNamed('mysql', \$this->subDomain)",
            File::get(base_path('app/Services/DBWhiteLabelService.php'))
        );
        $this->assertStringContainsString(
            "\$connections->configureNamed('mysql', \$company)",
            File::get(base_path('app/Http/Controllers/RelevanceReactorController.php'))
        );
    }

    public function test_base_install_sql_resolver_is_used_by_provisioning_and_legacy_imports(): void
    {
        $resolver = File::get(base_path('app/Services/BaseInstallSql.php'));
        $provisioning = File::get(base_path('app/Services/CompanyProvisioningService.php'));
        $legacyImport = File::get(base_path('app/Console/Commands/MigrateLegacyDatabase.php'));
        $legacySetup = File::get(base_path('src/System/Setup.php'));

        $this->assertSame(base_path('base_install.sql'), (new BaseInstallSql())->path());
        $this->assertStringContainsString('Unable to find base_install.sql.', $resolver);
        $this->assertStringContainsString("'base_install_sql' => env('TYS_BASE_INSTALL')", File::get(config_path('provisioning.php')));
        $this->assertStringContainsString("config('provisioning.base_install_sql')", $resolver);
        $this->assertStringNotContainsString("env('TYS_BASE_INSTALL", $resolver);
        $this->assertStringContainsString('BaseInstallSql $baseInstallSql', $provisioning);
        $this->assertStringContainsString('$this->baseInstallSql->path()', $provisioning);
        $this->assertStringContainsString('$this->baseInstallSql->contents($schemaPath)', $provisioning);
        $this->assertStringNotContainsString('private function baseInstallPath', $provisioning);

        $this->assertStringContainsString('BaseInstallSql $baseInstallSql', $legacyImport);
        $this->assertStringContainsString('$this->baseInstallSql->path()', $legacyImport);
        $this->assertStringContainsString('$this->baseInstallSql->contents()', $legacyImport);
        $this->assertStringNotContainsString("env('TYS_BASE_INSTALL", $legacyImport);

        $this->assertStringContainsString('BaseInstallSql', $legacySetup);
        $this->assertStringContainsString('TenantDatabasePdoFactory', $legacySetup);
        $this->assertStringContainsString('$this->baseInstallSql->contents()', $legacySetup);
        $this->assertStringContainsString('$this->databases->quoteIdentifier($this->subDomain())', $legacySetup);
        $this->assertStringNotContainsString('resources/importDB.php', $legacySetup);
        $this->assertStringNotContainsString('tys_create_db', $legacySetup);
    }

    public function test_base_install_sql_resolver_prefers_configured_absolute_path(): void
    {
        $path = sys_get_temp_dir() . '/tys-base-install-test.sql';

        try {
            File::put($path, 'select 1;');
            Config::set('provisioning.base_install_sql', $path);

            $resolver = new BaseInstallSql();

            $this->assertSame($path, $resolver->path());
            $this->assertSame('select 1;', $resolver->contents());
        } finally {
            File::delete($path);
        }
    }

    public function test_base_install_sql_resolver_treats_configured_relative_path_as_storage_relative(): void
    {
        $relativePath = 'tys-base-install-relative-test.sql';
        $path = storage_path($relativePath);

        try {
            File::put($path, 'select 2;');
            Config::set('provisioning.base_install_sql', $relativePath);

            $resolver = new BaseInstallSql();

            $this->assertSame($path, $resolver->path());
            $this->assertSame('select 2;', $resolver->contents());
        } finally {
            File::delete($path);
        }
    }

    public function test_base_install_sql_resolver_falls_back_when_configured_path_is_missing(): void
    {
        Config::set('provisioning.base_install_sql', '/tmp/missing-tys-base-install-test.sql');

        $this->assertSame(base_path('base_install.sql'), (new BaseInstallSql())->path());
    }

    public function test_tenant_database_pdo_factory_builds_connection_details_for_provisioning(): void
    {
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => 'db-host',
            'port' => '3307',
            'database' => 'master_db',
            'username' => 'db-user',
            'password' => 'db-pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        $factory = new TenantDatabasePdoFactory();

        $this->assertSame('mysql:host=db-host;port=3307', $factory->dsn());
        $this->assertSame('mysql:host=db-host;port=3307;dbname=tenant_a', $factory->dsn('tenant_a'));
        $this->assertSame('`tenant``name`', $factory->quoteIdentifier('tenant`name'));
        $this->assertFalse($factory->options()[\PDO::ATTR_EMULATE_PREPARES]);
    }

    public function test_company_provisioning_uses_database_factory_boundaries(): void
    {
        $provisioning = File::get(base_path('app/Services/CompanyProvisioningService.php'));

        $this->assertStringContainsString('TenantDatabasePdoFactory $databases', $provisioning);
        $this->assertStringContainsString('$this->databases->make()', $provisioning);
        $this->assertStringContainsString('$this->databases->make($subDomain)', $provisioning);
        $this->assertStringContainsString('$this->databases->quoteIdentifier($subDomain)', $provisioning);
        $this->assertStringContainsString('$company = $this->newCompany($data, $subDomain);', $provisioning);
        $this->assertStringContainsString('private function newCompany(array $data, string $subDomain): Company', $provisioning);
        $this->assertStringNotContainsString('new PDO', $provisioning);
        $this->assertStringNotContainsString('PDO::MYSQL_ATTR_MULTI_STATEMENTS', $provisioning);
    }

    public function test_company_provisioning_builds_company_with_defaults_without_database_side_effects(): void
    {
        $method = new ReflectionMethod(\App\Services\CompanyProvisioningService::class, 'newCompany');
        $method->setAccessible(true);

        $company = $method->invoke(
            app(\App\Services\CompanyProvisioningService::class),
            [
                'shortHand' => 'Acme',
                'companyName' => 'Acme Affiliates',
                'city' => 'Chicago',
                'state' => 'IL',
                'address' => '123 Main',
                'zip' => '60601',
                'telephone' => '555-0100',
                'email' => 'admin@example.test',
                'skype' => 'legacy-messenger',
                'allow_register' => '0',
            ],
            'tenant_a'
        );

        $this->assertSame('Acme', $company->shortHand);
        $this->assertSame('tenant_a', $company->subDomain);
        $this->assertSame('Acme Affiliates', $company->companyName);
        $this->assertSame('Telegram', $company->messenger_type);
        $this->assertSame('legacy-messenger', $company->messenger_username);
        $this->assertSame('', $company->login_url);
        $this->assertSame('', $company->landing_page);
        $this->assertSame('', $company->login_theme);
        $this->assertFalse((bool) $company->allow_register);
        $this->assertSame(0, $company->db_version);
        $this->assertNotSame('', $company->uid);
    }

    public function test_legacy_salt_helper_generates_random_strings_on_modern_php(): void
    {
        $salt = salt(12, true);

        $this->assertSame(12, strlen($salt));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', $salt);
    }

    public function test_white_label_database_switching_uses_connection_manager(): void
    {
        $connections = new FakeCompanyDatabaseConnectionManager();
        $service = new \App\Services\DBWhiteLabelService('tenant-a.example.test', $connections);
        $service->subDomain = 'tenant_a';

        $service->changeDatabaseHostWithSubDomain();

        $this->assertSame([
            [
                'connectionName' => 'mysql',
                'database' => 'tenant_a',
            ],
        ], $connections->configuredNames);
    }

    public function test_white_label_subdomain_is_derived_from_the_explicit_host(): void
    {
        $this->assertSame('tenant-a', \App\Services\DBWhiteLabelService::getSubDomain('Tenant-A.Example.Test:8443'));
        $this->assertSame('tenant-b', \App\Services\DBWhiteLabelService::getSubDomain('www.tenant-b.example.test.'));
    }

    public function test_migration_commands_expose_safe_selection_and_pretend_options(): void
    {
        $allInstalls = File::get(base_path('app/Console/Commands/MigrateAllInstalls.php'));
        $singleCompany = File::get(base_path('app/Console/Commands/MigrateSingleCompany.php'));

        $this->assertStringContainsString('{--company=*', $allInstalls);
        $this->assertStringContainsString('{--pretend', $singleCompany);
    }

    public function test_migrate_all_runs_only_selected_companies_and_passes_pretend_to_migrate(): void
    {
        $this->createMasterCompanyTable(['tenant-b', 'tenant-a', 'tenant-c']);

        $connections = new FakeCompanyDatabaseConnectionManager();
        $command = new TestMigrateAllInstalls($connections);
        $command->setLaravel(app());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--company' => [' tenant-c ', 'tenant-a', 'tenant-c'],
            '--pretend' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertSame(['tenant-a', 'tenant-c'], $connections->configured);
        $this->assertSame([
            [
                'command' => 'migrate',
                'arguments' => [
                    '--database' => 'tenant-a',
                    '--force' => true,
                    '--pretend' => true,
                ],
            ],
            [
                'command' => 'migrate',
                'arguments' => [
                    '--database' => 'tenant-c',
                    '--force' => true,
                    '--pretend' => true,
                ],
            ],
        ], $command->calls);
    }

    public function test_migrate_all_fails_when_a_selected_company_is_unknown(): void
    {
        $this->createMasterCompanyTable(['tenant-a']);

        $connections = new FakeCompanyDatabaseConnectionManager();
        $command = new TestMigrateAllInstalls($connections);
        $command->setLaravel(app());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            '--company' => ['tenant-a', 'missing-tenant'],
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Unknown company subdomain(s): missing-tenant', $tester->getDisplay());
        $this->assertSame([], $connections->configured);
        $this->assertSame([], $command->calls);
    }

    public function test_migrate_single_fails_when_company_is_unknown_without_calling_migrate(): void
    {
        $this->createMasterCompanyTable(['tenant-a']);

        $connections = new FakeCompanyDatabaseConnectionManager();
        $command = new TestMigrateSingleCompany($connections);
        $command->setLaravel(app());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'company' => 'missing-tenant',
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Unable to find company "missing-tenant".', $tester->getDisplay());
        $this->assertSame([], $connections->configured);
        $this->assertSame([], $command->calls);
    }

    public function test_migrate_single_runs_selected_company_and_passes_pretend_to_migrate(): void
    {
        $this->createMasterCompanyTable(['tenant-a', 'tenant-b']);

        $connections = new FakeCompanyDatabaseConnectionManager();
        $command = new TestMigrateSingleCompany($connections);
        $command->setLaravel(app());
        $tester = new CommandTester($command);

        $exitCode = $tester->execute([
            'company' => 'tenant-b',
            '--pretend' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertSame(['tenant-b'], $connections->configured);
        $this->assertSame([
            [
                'command' => 'migrate',
                'arguments' => [
                    '--database' => 'tenant-b',
                    '--force' => true,
                    '--pretend' => true,
                ],
            ],
        ], $command->calls);
    }

    private function createMasterCompanyTable(array $subDomains): void
    {
        Config::set('database.connections.master', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        DB::purge('master');

        Schema::connection('master')->create('company', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subDomain');
        });

        foreach ($subDomains as $subDomain) {
            $company = new Company();
            $company->subDomain = $subDomain;
            $company->save();
        }
    }
}

class FakeCompanyDatabaseConnectionManager extends CompanyDatabaseConnectionManager
{
    public $configured = [];
    public $configuredNames = [];

    public function configure(Company $company): string
    {
        $this->configured[] = $company->subDomain;

        return $company->subDomain;
    }

    public function configureNamed(string $connectionName, string $database): string
    {
        $this->configuredNames[] = compact('connectionName', 'database');

        return $connectionName;
    }
}

class TestMigrateAllInstalls extends MigrateAllInstalls
{
    public $calls = [];

    public function call($command, array $arguments = [])
    {
        $this->calls[] = compact('command', 'arguments');

        return Command::SUCCESS;
    }
}

class TestMigrateSingleCompany extends MigrateSingleCompany
{
    public $calls = [];

    public function call($command, array $arguments = [])
    {
        $this->calls[] = compact('command', 'arguments');

        return Command::SUCCESS;
    }
}
