<?php

namespace LeadMax\TrackYourStats\System;

use function Couchbase\defaultDecoder;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use PDO;

/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/17/2017
 * Time: 3:29 PM
 */
//class to handle connection to db based on sub-domain of the site.

class Connection


{

    public $subDomain = "ctpupgrade";

    private static $host;

    private static $user;

    private static $password;

    private static $port;

    public $wasOfferUrl = false;

    public function __construct()
    {
//		if ($this->alreadyLoaded())
//		{
//			return;
//		}

        $this->loadEnv();
        if ( ! $this->isOfferUrl() ) //checks if its an offer url
        {
            if ( ! $this->isLoginPage() && ! $this->isLanderPage() ) {

                $this->setSub(Company::getSub());
            } //if its not local or an offer url, must be an install
        }

        // checks if its on live test server (test.trackyourstats.com)
        // this is required because 'test' database name was taken.
        if ($this->isDev()) {
            $this->setDev();
        }

    }

    private function loadEnv()
    {
        self::$host = env("DB_HOST");
        self::$user = env("DB_USERNAME");
        self::$password = env("DB_PASSWORD");
        self::$port = env("DB_PORT");
    }

    private function alreadyLoaded()
    {
        if (isset($_SESSION["COMPANY_SUBDOMAIN"])) {


            $this->setSub($_SESSION["COMPANY_SUBDOMAIN"]);

            return true;
        }

        return false;
    }

    public static function createConnectionWithSubDomain($SUB_DOMAIN, $forceLive = false)
    {
        if (!$forceLive) {
            return new PDO( DB_TYPE . ":host=" . LOCALHOST . ";port=" . self::$port . ";dbname=" . $SUB_DOMAIN, DB_USERNAME, DB_PASSWORD);
        } else {
            return new PDO( DB_TYPE . ":host=" . self::$host . ";port=" . self::$port . ";dbname=" . $SUB_DOMAIN, DB_USERNAME, DB_PASSWORD);
        }
    }

    public function isLoginPage()
    {
        $db = DatabaseConnection::getMasterInstance();
        $sql = "SELECT subDomain FROM ". env('DB_DATABASE') . ".company WHERE login_url = :url";
        $prep = $db->prepare($sql);
        $loginURL = $_SERVER["HTTP_HOST"];
        $prep->bindParam(":url", $loginURL);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            $this->setSub($prep->fetch(PDO::FETCH_ASSOC)["subDomain"]);

            return true;
        }

        return false;
    }


    // checks if the current url is an offer url for a company
    public function isOfferUrl()
    {

        $db = DatabaseConnection::getMasterInstance();
        $sql = "SELECT * FROM ". env('DB_DATABASE') . ".offer_urls WHERE url = :url";
        $prep = $db->prepare($sql);
        $prep->bindParam(":url", $_SERVER["HTTP_HOST"]);
        $prep->execute();
        $foundOfferUrl = $prep->rowCount();

        // if it was a company's offer url, find their company id and fetch their sub-domain to connect to the proper db
        if ($foundOfferUrl > 0) //offerurl was found in db
        {

            $offerUrlEntry = $prep->fetch(PDO::FETCH_ASSOC);

            $sqlC = "SELECT subDomain FROM ". env('DB_DATABASE') . ".company WHERE id = :id";

            $prep = $db->prepare($sqlC);
            $prep->bindParam(":id", $offerUrlEntry["company_id"]);
            $prep->execute();
            $result = $prep->fetch(PDO::FETCH_ASSOC);

            $this->setSub($result["subDomain"]);

            $this->wasOfferUrl = true;

            return true;


        }


        return false;
    }


    // sets connection for class_dbcon
    public function setConnection()
    {
        $_SESSION["COMPANY_SUBDOMAIN"] = $this->subDomain;

        define('LOCALHOST', self::$host);
        //define("DB_NAME", $this->subDomain);
	    define("DB_NAME", env('DB_DATABASE'));

        define("DB_USERNAME", self::$user);
        define("DB_PASSWORD", self::$password);

        define('DB_TYPE', 'mysql');

    }

    public function setSub($sub)
    {
        $this->subDomain = $sub;
    }

    public function setDev()
    {
        $this->subDomain = "debug";
    }

    public function setLocal()
    {
        self::$host = "127.0.0.1";
        $this->subDomain = "chattrackpro";
        self::$user = "default";
        self::$password = 'secret';
    }

    public function isDev()
    {
        return Company::getSub() == 'test';
    }

    public function isLocal()
    {
        $validLocalExtensions = [
            'test',
            'app',
            'fuckchrome',
        ];

        return in_array(Company::getExtension(), $validLocalExtensions);
    }

    //DEPRECATED
    static function isMaster()
    {
//        if (Company::getSub() == "trackyourstats") {
//
//            define('LOCALHOST', '208.94.65.205');
//            define('DB_USERNAME', 'trackyou');
//            define('DB_PASSWORD', 'ts7Qd5#2');
//            define('DB_NAME', 'trackyourstats');
//            define('DB_TYPE', 'mysql');
//            return true;
//        }
//        return false;
    }

    private function isLanderPage()
    {
        $db = DatabaseConnection::getMasterInstance();
        $sql = "SELECT subDomain FROM ". env('DB_DATABASE') . ".company WHERE landing_page = :url";
        $prep = $db->prepare($sql);
        $loginURL = $_SERVER["HTTP_HOST"];
        $prep->bindParam(":url", $loginURL);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            $this->setSub($prep->fetch(PDO::FETCH_ASSOC)["subDomain"]);

            return true;
        }

        return false;
    }

}
