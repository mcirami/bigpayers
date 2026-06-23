<?php

namespace App\Services;

class LegacyDatabaseConfig
{
    private static $databaseConfig;

    public static function mysqlConnection(): array
    {
        return self::connection('mysql');
    }

    public static function mysql(string $key)
    {
        return self::value('mysql', $key);
    }

    public static function master(string $key)
    {
        return self::value('master', $key);
    }

    public static function primaryDatabase(): string
    {
        return (string) self::mysql('database');
    }

    private static function connection(string $connection): array
    {
        if (self::configIsAvailable()) {
            return (array) config("database.connections.{$connection}");
        }

        return (array) (self::databaseConfig()['connections'][$connection] ?? []);
    }

    private static function value(string $connection, string $key)
    {
        if (self::configIsAvailable()) {
            return config("database.connections.{$connection}.{$key}");
        }

        return self::connection($connection)[$key] ?? null;
    }

    private static function configIsAvailable(): bool
    {
        if (! function_exists('app')) {
            return false;
        }

        try {
            $app = app();

            return method_exists($app, 'bound') && $app->bound('config');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private static function databaseConfig(): array
    {
        if (self::$databaseConfig === null) {
            self::$databaseConfig = require base_path('config/database.php');
        }

        return self::$databaseConfig;
    }
}
