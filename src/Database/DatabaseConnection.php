<?php

namespace LeadMax\TrackYourStats\Database;

use App\Services\LegacyDatabaseConfig;
use PDO;

class DatabaseConnection
{
    private static $instance = null;

    private static $instanceMaster = null;

    public static function changeConnection($db)
    {
        self::$instance = $db;
    }


    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new PDO(
                "mysql:host=".LegacyDatabaseConfig::mysql('host').";port=".LegacyDatabaseConfig::mysql('port').";dbname=".LegacyDatabaseConfig::mysql('database'),
                LegacyDatabaseConfig::mysql('username'),
                LegacyDatabaseConfig::mysql('password')
            );
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$instance;
    }


    public static function getMasterInstance()
    {
        if (!self::$instanceMaster) {
            self::$instanceMaster = new \PDO(
                "mysql:host=".LegacyDatabaseConfig::master('host').";port=".LegacyDatabaseConfig::master('port').";dbname=".LegacyDatabaseConfig::master('database'),
                LegacyDatabaseConfig::master('username'),
                LegacyDatabaseConfig::master('password')
            );
            self::$instanceMaster->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$instanceMaster->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$instanceMaster;
    }
}
