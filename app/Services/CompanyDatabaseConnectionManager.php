<?php

namespace App\Services;

use App\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CompanyDatabaseConnectionManager
{
    public function configure(Company $company): string
    {
        return $this->configureNamed($this->connectionName($company), $this->connectionName($company));
    }

    public function configureNamed(string $connectionName, string $database): string
    {
        Config::set("database.connections.{$connectionName}", $this->connectionConfig($database));
        DB::purge($connectionName);

        return $connectionName;
    }

    public function useAsDefault(Company $company): string
    {
        $connectionName = $this->configure($company);

        DB::setDefaultConnection($connectionName);

        return $connectionName;
    }

    public function connectionConfig(string $database): array
    {
        return array_merge(config('database.connections.mysql'), [
            'database' => $database,
        ]);
    }

    private function connectionName(Company $company): string
    {
        return (string) $company->subDomain;
    }
}
