<?php

namespace App\Console\Commands;

use App\Company;
use Illuminate\Console\Command;

class MigrateSingleCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:single {company}';

    /**i
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs migrates for a specific company.';

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
        $company = Company::where('subDomain', '=', $this->argument('company'))->first();
        \Config::set('database.connections.' . $company->subDomain, array(
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => $company->subDomain,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ));

        $this->info('Running migration for "' . $company->subDomain . '"');
        $this->call('migrate', array('--database' => $company->subDomain, '--force'));
    }
}
