<?php

namespace App\Console\Commands;

use App\Company;
use App\Services\CompanyDatabaseConnectionManager;
use Illuminate\Console\Command;

class MigrateAllInstalls extends Command
{
    private $connections;

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
        foreach (Company::all() as $company) {
            $connectionName = $this->connections->configure($company);

            $this->info('Running migration for "' . $connectionName . '"');
            $this->call('migrate', ['--database' => $connectionName, '--force' => true]);
        }

        return 0;
    }
}
