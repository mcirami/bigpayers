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
    protected $signature = 'migrate:all
        {--company=* : Limit migrations to one or more company subdomains}
        {--pretend : Dump the SQL queries that would be run without executing them}';

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
        $requestedSubDomains = $this->selectedSubDomains();
        $companies = $this->companies($requestedSubDomains);
        $missingSubDomains = array_values(array_diff($requestedSubDomains, $companies->pluck('subDomain')->all()));

        if ($missingSubDomains) {
            $this->error('Unknown company subdomain(s): ' . implode(', ', $missingSubDomains));

            return self::FAILURE;
        }

        if ($companies->isEmpty()) {
            $this->warn('No companies matched the migration filters.');

            return self::SUCCESS;
        }

        foreach ($companies as $company) {
            $connectionName = $this->connections->configure($company);

            $this->info('Running migration for "' . $connectionName . '"');
            $this->call('migrate', [
                '--database' => $connectionName,
                '--force' => true,
                '--pretend' => (bool) $this->option('pretend'),
            ]);
        }

        return self::SUCCESS;
    }

    private function companies(array $subDomains = [])
    {
        $query = Company::query();

        if ($subDomains) {
            $query->whereIn('subDomain', $subDomains);
        }

        return $query->orderBy('subDomain')->get();
    }

    private function selectedSubDomains(): array
    {
        return collect((array) $this->option('company'))
            ->map(fn (string $subDomain) => trim($subDomain))
            ->filter(fn (string $subDomain) => $subDomain !== '')
            ->unique()
            ->values()
            ->all();
    }
}
