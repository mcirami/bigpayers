<?php

namespace App\Services;

use App\Company;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use PDO;
use RuntimeException;

class CompanyProvisioningService
{
    private const DEFAULT_COLORS = '484848;FFFFFF;2A58AD;1D4C9E;82A7EB;FCED16;EAEEF1;FFFFFF;404452;999999;1D4C9E';

    private $baseInstallSql;
    private $databases;

    public function __construct(BaseInstallSql $baseInstallSql, TenantDatabasePdoFactory $databases)
    {
        $this->baseInstallSql = $baseInstallSql;
        $this->databases = $databases;
    }

    public function provision(array $data): array
    {
        $subDomain = $this->normalizeSubDomain($data['subDomain']);

        if (Company::where('subDomain', $subDomain)->exists()) {
            throw new RuntimeException("An install already exists for {$subDomain}.");
        }

        $schemaPath = $this->baseInstallSql->path();
        $server = $this->databases->make();

        if ($this->databaseExists($server, $subDomain)) {
            throw new RuntimeException("A database named {$subDomain} already exists.");
        }

        $server->exec('CREATE DATABASE ' . $this->databases->quoteIdentifier($subDomain));

        $tenant = $this->databases->make($subDomain);
        $tenant->exec($this->baseInstallSql->contents($schemaPath));
        $this->updateBootstrapAdmin($tenant, $data);

        $company = $this->newCompany($data, $subDomain);
        $company->save();

        File::ensureDirectoryExists(public_path("images/{$subDomain}"));

        return [
            'company' => $company,
            'database' => $subDomain,
            'schemaPath' => $schemaPath,
            'adminUserName' => $data['userName'],
            'adminEmail' => $data['adminEmail'],
        ];
    }

    private function updateBootstrapAdmin(PDO $tenant, array $data): void
    {
        $statement = $tenant->prepare(
            'UPDATE rep SET email = :email, user_name = :user_name, password = :password WHERE idrep = 1'
        );

        $statement->execute([
            ':email' => $data['adminEmail'],
            ':user_name' => $data['userName'],
            ':password' => Hash::make($data['password']),
        ]);
    }

    private function newCompany(array $data, string $subDomain): Company
    {
        $company = new Company();
        $company->shortHand = $data['shortHand'];
        $company->subDomain = $subDomain;
        $company->companyName = $data['companyName'];
        $company->city = $data['city'];
        $company->state = $data['state'];
        $company->address = $data['address'];
        $company->zip = $data['zip'];
        $company->telephone = $data['telephone'];
        $company->email = $data['email'];
        $company->skype = $data['skype'] ?? '';
        $company->messenger_type = $data['messenger_type'] ?? 'Telegram';
        $company->messenger_username = $data['messenger_username'] ?? ($data['skype'] ?? '');
        $company->colors = self::DEFAULT_COLORS;
        $company->uid = salt(4, true);
        $company->db_version = 0;
        $company->login_url = $data['login_url'] ?? '';
        $company->landing_page = $data['landing_page'] ?? '';
        $company->login_theme = $data['login_theme'] ?? '';
        $company->allow_register = (bool) ($data['allow_register'] ?? true);

        return $company;
    }

    private function databaseExists(PDO $server, string $database): bool
    {
        $statement = $server->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database');
        $statement->execute([':database' => $database]);

        return (bool) $statement->fetchColumn();
    }

    private function normalizeSubDomain(string $subDomain): string
    {
        return strtolower(trim($subDomain));
    }
}
