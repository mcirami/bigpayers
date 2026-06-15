<?php namespace LeadMax\TrackYourStats\System;

use App\Support\LegacyUser as User;
use App\Support\NativeRequest;
use App\Support\NativeSession;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 9/27/2017
 * Time: 10:58 AM
 */
/*

 Class to load userData, permissions, etc From Session.

Also as a reference to know what stuff is being stored in session.


 */

class Session
{

    private static function isAdminLogin($requestedSessionVar, $unserialize = false)
    {
        $adminLogin = NativeSession::get('adminLogin');

        if (NativeRequest::hasQuery('adminLogin') && $adminLogin !== null) {
            if (isset($adminLogin[$requestedSessionVar])) {
                if ($unserialize) {
                    return unserialize($adminLogin[$requestedSessionVar]);
                } else {
                    return $adminLogin[$requestedSessionVar];
                }
            }
        } else {
            $sessionValue = NativeSession::get($requestedSessionVar);

            if ($sessionValue !== null) {
                if ($unserialize) {
                    return unserialize($sessionValue);
                } else {
                    return $sessionValue;
                }
            }
        }

        return false;
    }


    public static function userData()
    {
        return self::isAdminLogin('userData', true);
    }


    public static function permissions()
    {
        return self::isAdminLogin('permissions', true);
    }

    public static function userType()
    {
        return self::isAdminLogin('userType');
    }

    public static function userID()
    {
        return self::isAdminLogin('repid');
    }

    public static function user()
    {
        return \App\User::where('idrep', '=', static::userID())->first();
    }

}
