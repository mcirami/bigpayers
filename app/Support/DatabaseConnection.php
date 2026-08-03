<?php

namespace App\Support;

use App\Services\RuntimeDatabaseConfig;
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
                "mysql:host=".RuntimeDatabaseConfig::mysql('host').";port=".RuntimeDatabaseConfig::mysql('port').";dbname=".RuntimeDatabaseConfig::mysql('database'),
                RuntimeDatabaseConfig::mysql('username'),
                RuntimeDatabaseConfig::mysql('password')
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
                "mysql:host=".RuntimeDatabaseConfig::master('host').";port=".RuntimeDatabaseConfig::master('port').";dbname=".RuntimeDatabaseConfig::master('database'),
                RuntimeDatabaseConfig::master('username'),
                RuntimeDatabaseConfig::master('password')
            );
            self::$instanceMaster->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$instanceMaster->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$instanceMaster;
    }
}
