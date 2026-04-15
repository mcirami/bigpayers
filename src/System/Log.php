<?php


// php file used to store our log functions, these functions log to the database,
// there are two log functions, one that is just an informational log which logs to table "logs" in db,
// the other is error_log, which logs to the error_logs table in db.


function LogDB($error, $class)
{   //gets full url of page that error happened on
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    //gets class name of class that error happened, set to null if not in class
    if ($class != null) {
        $Class = get_class($class);
    } else {
        $Class = "not in class";
    }

    //get unix time stamp
    $time = date("U");

    //gets ip of user
    $ip = $_SERVER["REMOTE_ADDR"];

    $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


    $error = substr($error, 0, 254);


    $sql = "INSERT INTO log (class, description,url, time_stamp, ip) VALUES(:class, :error, :url, :time, :ip);";

    $prep = $db->prepare($sql);

    $prep->bindParam(":class", $Class);
    $prep->bindParam(":error", $error);
    $prep->bindParam(":url", $actual_link);
    $prep->bindParam(":time", $time);
    $prep->bindParam(":ip", $ip);

    if ($prep->execute()) {
        return true;
    } else {
        return false;
    }

}


//
function logError($error, $class)
{
    //gets full url of page that error happened on
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    //gets class name of class that error happened, set to null if not in class
    if ($class != null) {
        $Class = get_class($class);
    } else {
        $Class = "not in class";
    }

    //get unix time stamp
    $time = date("U");

    //gets ip of user
    $ip = $_SERVER["REMOTE_ADDR"];

    $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


    $sql = "INSERT INTO error_logs (class, error,url, time_stamp, ip) VALUES(:class, :error, :url, :time, :ip);";

    $prep = $db->prepare($sql);

    $prep->bindParam(":class", $Class);
    $prep->bindParam(":error", $error);
    $prep->bindParam(":url", $actual_link);
    $prep->bindParam(":time", $time);
    $prep->bindParam(":ip", $ip);

    if ($prep->execute()) {
        return true;
    } else {
        return false;
    }

}


