<?php

namespace App\Console\Commands;

use App\Services\BaseInstallSql;
use App\Services\CompanyDatabaseConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
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

        //        if ($this->ask('Do you want to delete the current master database? y/n', 'y') == 'y') {
        //            $this->info('Deleting current master database..');
        //            if (DB::connection('master')
        //                ->unprepared("select concat('drop table if exists ', table_name, ' cascade;')
        //                                    from information_schema.tables;")) {
        //               $this->info('Success!');
        //            }
        //        } else {
        //            $this->info('Jeez fine.');
        //        }

        Config::set('database.connections.importing', $this->connections->connectionConfig($database));
        DB::purge('importing');

        if (DB::connection('importing')->unprepared($this->baseInstallSql->contents())) {
            $this->info('Success!');

            return self::SUCCESS;
        } else {
            $this->error('Failed to import legacy database!');

            return self::FAILURE;
        }
    }
}
