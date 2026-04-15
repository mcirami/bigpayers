<?php

namespace LeadMax\TrackYourStats\Database;

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
            self::$instance = new PDO("mysql:host=".env('DB_HOST').";port=".env('DB_PORT').";dbname=".env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$instance;
    }


    public static function getMasterInstance()
    {
        if (!self::$instanceMaster) {
            self::$instanceMaster = new \PDO("mysql:host=".env('MASTER_DB_HOST').";port=".env('MASTER_DB_PORT').";dbname=".env('MASTER_DB_DATABASE'),
                env('MASTER_DB_USERNAME'), env('MASTER_DB_PASSWORD'));
            self::$instanceMaster->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$instanceMaster->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$instanceMaster;
    }
}
