<?php

namespace App\Support;

use LeadMax\TrackYourStats\System\Session;

class CurrentUserSession
{
    public static function id(): int
    {
        return (int) Session::userID();
    }

    public static function type(): int
    {
        return (int) Session::userType();
    }

    public static function user()
    {
        return Session::user();
    }

    public static function data()
    {
        return Session::userData();
    }

    public static function permissions()
    {
        return Session::permissions();
    }

    public static function can(string $permission): bool
    {
        return self::permissions()->can($permission);
    }
}
