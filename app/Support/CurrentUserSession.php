<?php

namespace App\Support;

use App\User;

class CurrentUserSession
{
    public static function id(): int
    {
        return (int) self::value('repid');
    }

    public static function type(): int
    {
        return (int) self::value('userType');
    }

    public static function user()
    {
        return User::query()->where('idrep', '=', self::id())->first();
    }

    public static function data()
    {
        return self::value('userData', true);
    }

    public static function permissions()
    {
        return self::value('permissions', true);
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

    private static function value(string $key, bool $unserialize = false)
    {
        $adminLogin = NativeSession::get('adminLogin');

        if (NativeRequest::hasQuery('adminLogin') && $adminLogin !== null) {
            $value = $adminLogin[$key] ?? null;
        } else {
            $value = NativeSession::get($key);
        }

        if ($value === null) {
            return false;
        }

        return $unserialize ? unserialize($value) : $value;
    }
}
