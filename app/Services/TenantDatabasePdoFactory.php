<?php

namespace App\Services;

use PDO;

class TenantDatabasePdoFactory
{
    public function make(?string $database = null): PDO
    {
        $config = config('database.connections.mysql');

        return new PDO(
            $this->dsn($database),
            $config['username'],
            $config['password'],
            $this->options()
        );
    }

    public function dsn(?string $database = null): string
    {
        $config = config('database.connections.mysql');
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'];

        if ($database !== null) {
            $dsn .= ';dbname=' . $database;
        }

        return $dsn;
    }

    public function options(): array
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
            $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
        }

        return $options;
    }

    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
