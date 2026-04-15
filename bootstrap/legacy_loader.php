<?php
use Dotenv\Dotenv;

// composer auto load
include __DIR__. "/../vendor/autoload.php";

// .env

$dotEnv = Dotenv::createImmutable(__DIR__.'/..');
$dotEnv->load();

// set default timezone
    date_default_timezone_set(env('TIMEZONE'));


    if (env('APP_DEBUG')) {
        set_error_handler("handle_error");
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

    }

    session_start();


//  TYS Install Connection
    $con = new \LeadMax\TrackYourStats\System\Connection();
    $con->setConnection();

//	unset($_SESSION["company"]);

// find company information
    $company = LeadMax\TrackYourStats\System\Company::loadFromSession();
    $company->setSession();
