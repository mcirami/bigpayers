<?php

namespace Tests\Feature;

use App\Company;
use App\Services\CompanyDatabaseConnectionManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

    public function test_migration_commands_use_the_company_database_connection_manager(): void
    {
        $commands = [
            File::get(base_path('app/Console/Commands/AggregateReportData.php')),
            File::get(base_path('app/Console/Commands/MigrateAllInstalls.php')),
            File::get(base_path('app/Console/Commands/MigrateSingleCompany.php')),
            File::get(base_path('app/Console/Commands/PayoutLogsRun.php')),
        ];

        foreach ($commands as $commandSource) {
            $this->assertStringContainsString(CompanyDatabaseConnectionManager::class, $commandSource);
            $this->assertStringNotContainsString('Config::set', $commandSource);
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
            '$this->connections->useAsDefault($company)',
            File::get(base_path('app/Console/Commands/AggregateReportData.php'))
        );
        $this->assertStringContainsString(
            '$this->connections->useAsDefault($company)',
            File::get(base_path('app/Console/Commands/PayoutLogsRun.php'))
        );
    }

    public function test_single_company_migration_handles_unknown_companies(): void
    {
        $singleCompany = File::get(base_path('app/Console/Commands/MigrateSingleCompany.php'));

        $this->assertStringContainsString("Unable to find company", $singleCompany);
        $this->assertStringContainsString('return 1;', $singleCompany);
    }
}
