# Tenant Migrations

Tenant migration commands run Laravel migrations against existing company
databases listed in the master `company` table. They do not create new tenant
databases or import `base_install.sql`.

## Commands

Run all known company installs:

```bash
php artisan migrate:all
```

Run only selected company subdomains:

```bash
php artisan migrate:all --company=tenant-a --company=tenant-b
```

Run one company:

```bash
php artisan migrate:single tenant-a
```

Preview SQL without applying migrations:

```bash
php artisan migrate:all --company=tenant-a --pretend
php artisan migrate:single tenant-a --pretend
```

## Connection Setup

`App\Services\CompanyDatabaseConnectionManager` builds tenant connections from
`database.connections.mysql` and swaps only the database name to the company
subdomain. This keeps host, port, credentials, charset, strict mode, and engine
settings consistent with the primary tenant database configuration.

Commands that iterate over companies should use the connection manager instead
of calling `Config::set(...)` or reading `env(...)` directly.

## Provisioning Boundary

Creating a new company install is handled separately by
`App\Services\CompanyProvisioningService`. That flow creates the database,
imports `base_install.sql`, updates the bootstrap admin user, creates the master
company row, and prepares the public image directory.

Do not use the tenant migration commands as a provisioning substitute; they
expect the company database to already exist.

`php artisan migrate:legacy <database>` is a lower-level import helper for
loading `base_install.sql` into an already-selected database name. It uses the
same tenant connection settings as the migration commands, but it does not
create the database or company record.
