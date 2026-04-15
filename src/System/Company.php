<?php

namespace LeadMax\TrackYourStats\System;

use LeadMax\TrackYourStats\Database\DatabaseConnection;
use PDO;

// Class to handle company auto loading for installs, gets company info, colors, sub-domain, etc..
// company settings are stored into session, if we have an instance of company settings in session, we don't re-query the db for company info

class Company
{

    public $loaded = false;

    public $subDomain = "master";
    public $shortHand = "Track Your Stats";
    public $imgDir = "images\\trackyourstats";

    public $id;

    public $uid;

    public $skype = "";

    public $email = "";

    public $landing_page = "";

    public $login_url = "";

    public $colors = false;

    public $login_theme = '';


    function __construct()
    {


    }

    public static function loadFromSession()
    {
        if (isset($_SESSION["company"])) {
            return unserialize($_SESSION["company"]);
        } else {
            $company = new self;
            $company->setSession();

            return $company;
        }
    }


    public function isCompanyOfferUrl($url)
    {
        $offerUrls = self::getOfferUrls();

        if ( is_array($offerUrls) && ! empty( $offerUrls ) ) {
            foreach ($offerUrls as $offer_url) {
                if ($offer_url[0] === $url) {
                    return true;
                }
            }

            return false;
        } else {
            return false;
        }
    }


    //gets offer urls specific to that company
    static function getOfferUrls()
    {
        $db   = DatabaseConnection::getMasterInstance();
        $sql  = "SELECT url FROM offer_urls INNER JOIN company ON company.subDomain = :sub WHERE offer_urls.company_id = company.id AND offer_urls.status = 1";
        $prep = $db->prepare($sql);
        $sub  = static::getCustomSub();
        $prep->bindParam(":sub", $sub);
        $prep->execute();

        return $prep->fetchAll(PDO::FETCH_NUM);
    }


    //gets sub domain of current host
    static function getSub()
    {
	    return env("DB_DATABASE");
       /* $sub = explode(".", $_SERVER["HTTP_HOST"]);

		if ($sub[0] === "www" || is_numeric($sub[0]) ) {
			return env("DB_DATABASE");
		}
        return $sub[0];*/
    }

    static function getCustomSub()
    {
	    return $_SESSION["COMPANY_SUBDOMAIN"] ?? self::getSub();
    }


    //gets extension of current host,
    // INPUT: trackyourstats.com
    // OUTPUT: com
    static function getExtension()
    {
        $sub = explode(".", $_SERVER["HTTP_HOST"]);

        return $sub[count($sub) - 1];
    }


    //Input: Color String in format : #1234;#1234;#1234; (sepperated by commas)
    //Output: Array
    function getColorArray($colorStr)
    {
        return explode(";", $colorStr);
    }

    //different functions will change loaded boolean to determine whether or not we have shit instanciated
    public function isLoaded()
    {
        return ($this->loaded) ? true : false;
    }

    public function loaded()
    {
        $this->loaded = true;
    }


    public function setSession()
    {
        if ( ! isset($_SESSION["company"])) {


            $this->loadCompany();
            $this->loaded();

            $_SESSION["company"] = serialize($this);
        }
    }


    public function getLandingPage()
    {
        if ($this->isLoaded()) {
            return $this->landing_page;
        }

        return false;
    }


    public function getLoginURL()
    {
        if ($this->isLoaded()) {
            return $this->login_url;
        }

        return false;
    }

    public function getShortHand()
    {
        if ($this->isLoaded()) {
            return $this->shortHand;
        }

        return false;
    }

    public function getSubDomain()
    {
        if ($this->isLoaded()) {
            return $this->subDomain;
        }

        return false;
    }

    public function getUID()
    {
        if ($this->isLoaded()) {
            return $this->uid;
        }

        return false;
    }

    public function getImgDir()
    {
        return "images/" . $this->subDomain;
    }

    public function getColors()
    {
        if ($this->isLoaded()) {
            return $this->colors;
        }

        return false;
    }

    public function getSkype()
    {
        if ($this->isLoaded()) {
            return $this->skype;
        }

        return false;
    }

    public function getEmail()
    {
        if ($this->isLoaded()) {
            return $this->email;
        }

        return false;
    }


    public function getID()
    {
        if ($this->isLoaded()) {
            return $this->id;
        }

        return false;
    }


    public function loadCompany()
    {
        try {
            $db  = DatabaseConnection::getMasterInstance();
            $sql = "SELECT * FROM company WHERE subDomain = :subDomain";

            $prep = $db->prepare($sql);
            $sub  = Company::getCustomSub();
            $prep->bindParam(":subDomain", $sub);


            $prep->execute();


            $company = $prep->fetch(PDO::FETCH_ASSOC);

            $this->uid = $company["uid"] ?? '';

            $this->id = $company["id"]  ?? '';

            $this->shortHand = $company["shortHand"] ?? '';
            $this->imgDir    = $this->getImgDir();

            $this->subDomain = $this->getCustomSub();

            $this->skype = $company["skype"] ?? '';

            $this->email = $company["email"] ?? '';

            $this->colors = $this->getColorArray($company["colors"] ?? '');

            $this->landing_page = $company["landing_page"] ?? '';


            $this->login_url = $company["login_url"]  ?? '';

            $this->login_theme = $company['login_theme'] ?? '';

            $this->loaded();


            return true;
        } catch (\Exception $e) {
            $this->loaded = false;

            return false;

        }


    }

    //deletes session and reloads
    public function reloadSettings()
    {
        unset($_SESSION["company"]);
        $this->setSession();
    }

    public function updateCompany($shortHand, $colors, $email, $skype, $loginURL, $landingPage)
    {
        try {
            $db   = DatabaseConnection::getMasterInstance();
            $sql  = "UPDATE company SET shortHand = :shortHand, colors = :colors, email = :email, skype = :skype, login_url = :loginURL, landing_page = :landingPage WHERE subDomain = :subDomain";
            $prep = $db->prepare($sql);
            $prep->bindParam(":shortHand", $shortHand);
            $prep->bindParam(":colors", $colors);
            $prep->bindParam(":subDomain", $this->subDomain);
            $prep->bindParam(":email", $email);
            $prep->bindParam(":skype", $skype);
            $prep->bindParam(":loginURL", $loginURL);
            $prep->bindParam(":landingPage", $landingPage);


            $prep->execute();
            $this->reloadSettings();

            return true;
        } catch (\Exception $e) {
            return false;
        }


    }


}