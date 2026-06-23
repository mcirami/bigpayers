<?php

namespace App\Support;

use App\User;
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
        return User::query()->where('idrep', '=', self::id())->first();
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

    public static function snapshot(): CurrentUserContext
    {
        $id = self::id();

        return new CurrentUserContext(
            $id,
            self::type(),
            self::data(),
            self::permissions(),
        );
    }
}
