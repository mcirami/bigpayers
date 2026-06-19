<?php

namespace Tests\Feature;

use App\Company;
use App\Console\Commands\MigrateAllInstalls;
use App\Console\Commands\MigrateSingleCompany;
use App\Services\BaseInstallSql;
use App\Services\CompanyDatabaseConnectionManager;
use App\Services\TenantDatabasePdoFactory;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
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

        $this->assertSame(base_path('base_install.sql'), (new BaseInstallSql())->path());
        $this->assertStringContainsString('Unable to find base_install.sql.', $resolver);
        $this->assertStringContainsString('BaseInstallSql $baseInstallSql', $provisioning);
        $this->assertStringContainsString('$this->baseInstallSql->path()', $provisioning);
        $this->assertStringContainsString('$this->baseInstallSql->contents($schemaPath)', $provisioning);
        $this->assertStringNotContainsString('private function baseInstallPath', $provisioning);

        $this->assertStringContainsString('BaseInstallSql $baseInstallSql', $legacyImport);
        $this->assertStringContainsString('$this->baseInstallSql->path()', $legacyImport);
        $this->assertStringContainsString('$this->baseInstallSql->contents()', $legacyImport);
        $this->assertStringNotContainsString("env('TYS_BASE_INSTALL", $legacyImport);
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
