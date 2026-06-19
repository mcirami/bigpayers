<?php

namespace App\Console\Commands;

use App\Services\BaseInstallSql;
use App\Services\CompanyDatabaseConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MigrateLegacyDatabase extends Command
{
    private $connections;
    private $baseInstallSql;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy {database : Database name to import into}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the base_install.sql file into the specified database. NOTE: The legacy database dump should be transitioned into migrations!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CompanyDatabaseConnectionManager $connections, BaseInstallSql $baseInstallSql)
    {
        parent::__construct();

        $this->connections = $connections;
        $this->baseInstallSql = $baseInstallSql;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $database = (string) $this->argument('database');

        try {
            $path = $this->baseInstallSql->path();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Importing ' . $path);
        $this->info('To: ' . $database);

        $connectionName = $this->connections->configureNamed('importing', $database);

        if (DB::connection($connectionName)->unprepared($this->baseInstallSql->contents())) {
            $this->info('Success!');

            return self::SUCCESS;
        } else {
            $this->error('Failed to import legacy database!');

            return self::FAILURE;
        }
    }
}
