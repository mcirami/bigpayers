<?php

namespace App\Services;

class LegacyDatabaseConfig
{
    public static function mysql(string $key)
    {
        return config("database.connections.mysql.{$key}");
    }

    public static function master(string $key)
    {
        return config("database.connections.master.{$key}");
    }

    public static function primaryDatabase(): string
    {
        return (string) self::mysql('database');
    }
}
