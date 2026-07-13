<?php

namespace App\Support;

use LeadMax\TrackYourStats\System\Company;

class RuntimeBootstrap
{
    private static bool $bootstrapped = false;

    public static function boot(): void
    {
        if (self::$bootstrapped) {
            return;
        }

        self::$bootstrapped = true;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $connection = new LegacyConnection();
        $connection->setConnection();

        Company::loadFromSession()->setSession();
    }
}
