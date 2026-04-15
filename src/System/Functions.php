<?php
/*
HEZECOM CMS PRO v1.0
http://hezecom.com
info@hezecom.com
COPYRIGHT 2012 ALL RIGHTS RESERVED
*/


function findProtocol()
{
    $protocol = "http://";

    if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443) {
        $protocol = "https://";
    }

    return $protocol;
}


//gets the webroot of the site, this was original used in the design by matteo so i carried it over
function getWebRoot()
{
    $protocol = findProtocol();

    if (isset($_SERVER["HTTP_HOST"])) {

        if (($_SERVER["HTTP_HOST"] == "127.0.0.1" || $_SERVER["HTTP_HOST"] == "localhost")) {
            $webroot = $protocol.$_SERVER['HTTP_HOST'].'/trackyourstats/';
        } else {
            $webroot = $protocol.$_SERVER['HTTP_HOST'].'/';
        }
    } else {
        $webroot = "unknown";
    }


    return $webroot;

}

// php file to store commonly used functions in the site.

function multiDimentialToSingular($array)
{
    $singular = array();
    foreach ($array as $key => $val) {
        foreach ($val as $key2 => $val2) {
            $singular[] = $val2;
        }
    }

    return $singular;
}

function handle_error($errno, $errstr, $errfile, $errline)
{

    $error = "[$errno] $errstr || ON LINE: $errline IN $errfile";

    $Class = "$errno";

    echo "ERROR: [$errno] $errstr || ON LINE: $errline IN $errfile. ";


    //get unix time stamp
    $time = date("U");

    //gets ip of user
    $ip = $_SERVER["REMOTE_ADDR"];

    $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


    $sql = "INSERT INTO error_logs (class, error, time_stamp, ip, error_number, resolved) VALUES(:class, :error,  :time, :ip, :error_number, 0 );";

    $prep = $db->prepare($sql);

    $prep->bindParam(":class", $Class);
    $prep->bindParam(":error", $error);
    $prep->bindParam(":error_number", $errno);
    $prep->bindParam(":time", $time);
    $prep->bindParam(":ip", $ip);

    if ($prep->execute()) {
        return true;
    } else {
        return false;
    }

}


function isSelectedPage($pageName)
{
    Global $section;
    if ($pageName == $section) {
        return " active value_span1 value_span2 value_span6-1 ";
    }

    return "";

}

function charLimit($x, $length)
{
    if (strlen($x) <= $length) {
        return $x;
    } else {
        $y = substr($x, 0, $length).'...';

        return $y;
    }
}


function salt($max = 40, $numAndCharOnly = false)
{
    $i = 0;
    $salt = "";

    $characterList = "./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    if ($numAndCharOnly) {
        $characterList = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    }

    while ($i < $max) {
        $salt .= $characterList(mt_rand(0, (strlen($characterList) - 1)));
        $i++;
    }

    return $salt;
}


function alert($msg)
{
    echo "<script>alert({$msg}</script>";
}

//post
function post($var)
{
    if (isset($_POST[$var])) {
        return $_POST[$var];
    }
}


function xss_clean($data)
{
// Fix &entity\n;
    $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

// Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

// Remove javascript: and vbscript: protocols
    $data =
        preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
        '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
        '$1=$2nomozbinding...', $data);

// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>',
        $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>',
        $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
        '$1>', $data);

// Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
            '', $data);
    } while ($old_data !== $data);

// we are done...
    return $data;
}

function send_to($direction)
{

    if (isset($_GET["adminLogin"])) {
        if (strpos($direction, "?")) {
            $direction .= "&adminLogin";
        } else {
            $direction .= "?adminLogin";
        }
    }
    if (!headers_sent()) {
        header('Location: '.$direction);
        exit;
    } else {
    	//$url = "http://chattrackpro.test/" . $direction;
	    $string = '<script type="text/javascript">';
	    $string .= 'window.location = "' . $direction . '"';
	    $string .= '</script>';

	    echo $string;
    }

}

