<?php

namespace App\Console\Commands;

use App\Company;
use Illuminate\Console\Command;

class MigrateAllInstalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs migrations against all installs in master database.';

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
        foreach (Company::all() as $company) {
            \Config::set('database.connections.'.$company->subDomain, array(
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'database' => $company->subDomain,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ));
            
            $this->info('Running migration for "'.$company->subDomain.'"');
            $this->call('migrate', array('--database' => $company->subDomain, '--force' => '--force'));
        }
    }
}
