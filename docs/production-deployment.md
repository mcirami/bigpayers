# Production Deployment

Deploy the application as one complete release. Do not retain `public/index.php`
or generated files under `vendor/composer` from an earlier release.

## Repair a Mixed Deployment

The following symptoms mean that old and new release files were mixed:

- `public/index.php` tries to inspect or include `../legacy/index.php`
- Composer tries to require a file below the retired `src/` directory

Upload the complete current release first, including `public/index.php`,
`composer.json`, `composer.lock`, `app/`, `bootstrap/`, `routes/`, and `scripts/`.
Then run these commands from the production project root:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php scripts/verify-production-deploy.php
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

Use the PHP 8.4 and Composer executables configured for the domain in Plesk. If
Composer is managed through the Plesk UI, run its Install action and then run
the remaining PHP commands from the project root.

The verifier intentionally does not load `vendor/autoload.php`, so it can report
stale Composer metadata even when Laravel and Artisan cannot boot. Do not restore
or create placeholder files under `src/`; regenerate Composer's metadata from
the current `composer.json` instead.

If the same old stack trace remains after these steps, restart the domain's PHP
handler in Plesk to clear OPcache, then run the verifier again.

## Release Checks

Before switching traffic to a release, run:

```bash
php scripts/verify-production-deploy.php
php artisan legacy:audit-fallback-coverage
php vendor/bin/phpunit
```

The first command checks the deploy artifact without booting Laravel. The second
checks route and retired-code boundaries after the application can boot.
