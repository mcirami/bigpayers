<?php

namespace App\Support;

use App\Support\DatabaseConnection;
use App\Services\RuntimeDatabaseConfig;
use App\Support\NativeRequest;
use App\Support\NativeSession;
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

                $this->setSub(RuntimeCompany::getSub());
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
        self::$host = RuntimeDatabaseConfig::mysql('host');
        self::$user = RuntimeDatabaseConfig::mysql('username');
        self::$password = RuntimeDatabaseConfig::mysql('password');
        self::$port = RuntimeDatabaseConfig::mysql('port');
    }

    private function alreadyLoaded()
    {
        $subDomain = NativeSession::get('COMPANY_SUBDOMAIN');

        if ($subDomain !== null) {

            $this->setSub($subDomain);

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
        $sql = "SELECT subDomain FROM ". RuntimeDatabaseConfig::primaryDatabase() . ".company WHERE login_url IN (:url, :wwwUrl)";
        $prep = $db->prepare($sql);
        $loginURL = $this->normalizeHost(NativeRequest::host());
        $wwwLoginURL = 'www.' . $loginURL;
        $prep->bindParam(":url", $loginURL);
        $prep->bindParam(":wwwUrl", $wwwLoginURL);
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
        $sql = "SELECT * FROM ". RuntimeDatabaseConfig::primaryDatabase() . ".offer_urls WHERE url IN (:url, :wwwUrl)";
        $prep = $db->prepare($sql);
        $host = $this->normalizeHost(NativeRequest::host());
        $wwwHost = 'www.' . $host;
        $prep->bindParam(":url", $host);
        $prep->bindParam(":wwwUrl", $wwwHost);
        $prep->execute();
        $foundOfferUrl = $prep->rowCount();

        // if it was a company's offer url, find their company id and fetch their sub-domain to connect to the proper db
        if ($foundOfferUrl > 0) //offerurl was found in db
        {

            $offerUrlEntry = $prep->fetch(PDO::FETCH_ASSOC);

            $sqlC = "SELECT subDomain FROM ". RuntimeDatabaseConfig::primaryDatabase() . ".company WHERE id = :id";

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
        NativeSession::put('COMPANY_SUBDOMAIN', $this->subDomain);

        define('LOCALHOST', self::$host);
        //define("DB_NAME", $this->subDomain);
	    define("DB_NAME", RuntimeDatabaseConfig::primaryDatabase());

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
        return RuntimeCompany::getSub() == 'test';
    }

    public function isLocal()
    {
        $validLocalExtensions = [
            'test',
            'app',
            'fuckchrome',
        ];

        return in_array(RuntimeCompany::getExtension(), $validLocalExtensions);
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
        $sql = "SELECT subDomain FROM ". RuntimeDatabaseConfig::primaryDatabase() . ".company WHERE landing_page IN (:url, :wwwUrl)";
        $prep = $db->prepare($sql);
        $loginURL = $this->normalizeHost(NativeRequest::host());
        $wwwLoginURL = 'www.' . $loginURL;
        $prep->bindParam(":url", $loginURL);
        $prep->bindParam(":wwwUrl", $wwwLoginURL);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            $this->setSub($prep->fetch(PDO::FETCH_ASSOC)["subDomain"]);

            return true;
        }

        return false;
    }

    private function normalizeHost(string $host): string
    {
        $normalized = strtolower(trim($host));
        $normalized = preg_replace('/:\d+$/', '', $normalized);
        $normalized = rtrim($normalized, '.');

        if (str_starts_with($normalized, 'www.')) {
            return substr($normalized, 4);
        }

        return $normalized;
    }

}
