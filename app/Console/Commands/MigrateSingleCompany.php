<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\CompanyDatabaseConnectionManager;
use Illuminate\Console\Command;

class MigrateSingleCompany extends Command
{
    private $connections;

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
    public function __construct(CompanyDatabaseConnectionManager $connections)
    {
        parent::__construct();

        $this->connections = $connections;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $company = Company::where('subDomain', '=', $this->argument('company'))->first();

        if (!$company) {
            $this->error('Unable to find company "' . $this->argument('company') . '".');

            return 1;
        }

        $connectionName = $this->connections->configure($company);

        $this->info('Running migration for "' . $connectionName . '"');
        $this->call('migrate', ['--database' => $connectionName, '--force' => true]);

        return 0;
    }
}
