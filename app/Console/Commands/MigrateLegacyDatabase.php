<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:legacy {database}';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Importing ' . storage_path(env('TYS_BASE_INSTALL')));
        $this->info('To: ' . $this->argument('database'));


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

        // Set the database
        \Config::set('database.connections.importing', array(
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => $this->argument('database'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ));

        if (DB::connection('importing')->unprepared(file_get_contents(storage_path(env('TYS_BASE_INSTALL'))))) {
            $this->info('Success!');
        } else {
            $this->error('Failed to import legacy database!');

            return;
        }
    }
}
