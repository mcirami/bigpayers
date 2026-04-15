<?php namespace LeadMax\TrackYourStats\System;

use LeadMax\TrackYourStats\User\User;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 9/27/2017
 * Time: 10:58 AM
 */
/*

 Class to load userData, permissions, etc From Session.

Also as a reference to know what stuff is being stored in session.

$repid = $_SESSION["repid"];

$per = unserialize($_SESSION["permissions"]);

$userData = unserialize($_SESSION["userData"]);

$userType = $_SESSION["userType"];


 */

class Session
{

    private static function isAdminLogin($requestedSessionVar, $unserialize = false)
    {
        if (isset($_GET["adminLogin"]) && isset($_SESSION['adminLogin'])) {
            if (isset($_SESSION['adminLogin'][$requestedSessionVar])) {
                if ($unserialize) {
                    return unserialize($_SESSION["adminLogin"][$requestedSessionVar]);
                } else {
                    return $_SESSION["adminLogin"][$requestedSessionVar];
                }
            }
        } else {
            if (isset($_SESSION[$requestedSessionVar])) {
                if ($unserialize) {
                    return unserialize($_SESSION[$requestedSessionVar]);
                } else {
                    return $_SESSION[$requestedSessionVar];
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