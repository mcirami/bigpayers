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
    public $messenger_type = "";
    public $messenger_username = "";

    public $email = "";

    public $landing_page = "";

    public $login_url = "";

    public $colors = false;

    public $login_theme = '';
    public $allow_register = true;


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
        $normalizedUrl = $this->normalizeHost((string) $url);

        if ( is_array($offerUrls) && ! empty( $offerUrls ) ) {
            foreach ($offerUrls as $offer_url) {
                if ($this->normalizeHost((string) $offer_url[0]) === $normalizedUrl) {
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

    private function normalizeHost(string $host): string
    {
        $normalized = strtolower(trim($host));

        if (str_starts_with($normalized, 'www.')) {
            return substr($normalized, 4);
        }

        return $normalized;
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

    public function getBrandAssetUrl(string $filename): string
    {
        $relativePath = trim($this->getImgDir(), '/\\') . '/' . ltrim($filename, '/\\');
        $fullPath = public_path($relativePath);

        if (file_exists($fullPath)) {
            return '/' . $relativePath . '?v=' . filemtime($fullPath);
        }

        return '/' . $relativePath;
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

    public function getMessengerType()
    {
        if ($this->isLoaded()) {
            return $this->messenger_type !== '' ? $this->messenger_type : 'Telegram';
        }

        return false;
    }

    public function getMessengerUsername()
    {
        if ($this->isLoaded()) {
            return $this->messenger_username !== '' ? $this->messenger_username : $this->skype;
        }

        return false;
    }

    public function allowsRegister(): bool
    {
        if ($this->isLoaded()) {
            return (bool) $this->allow_register;
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
            $this->messenger_type = $company["messenger_type"] ?? '';
            $this->messenger_username = $company["messenger_username"] ?? '';

            $this->email = $company["email"] ?? '';

            $this->colors = $this->getColorArray($company["colors"] ?? '');

            $this->landing_page = $company["landing_page"] ?? '';


            $this->login_url = $company["login_url"]  ?? '';

            $this->login_theme = $company['login_theme'] ?? '';
            $this->allow_register = array_key_exists('allow_register', $company) ? (bool) $company['allow_register'] : true;

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

    public function updateCompany($shortHand, $colors, $email, $skype, $loginURL, $landingPage, $loginTheme = null, $messengerType = null, $messengerUsername = null, $allowRegister = null)
    {
        try {
            $db   = DatabaseConnection::getMasterInstance();
            $resolvedLoginTheme = $loginTheme ?? $this->login_theme;
            $resolvedMessengerType = $messengerType ?? $this->messenger_type;
            $resolvedMessengerUsername = $messengerUsername ?? $this->messenger_username ?? $skype;
            $resolvedAllowRegister = $allowRegister ?? $this->allow_register;
            $sql  = "UPDATE company SET shortHand = :shortHand, colors = :colors, email = :email, skype = :skype, messenger_type = :messengerType, messenger_username = :messengerUsername, login_url = :loginURL, landing_page = :landingPage, login_theme = :loginTheme, allow_register = :allowRegister WHERE subDomain = :subDomain";
            $prep = $db->prepare($sql);
            $prep->bindParam(":shortHand", $shortHand);
            $prep->bindParam(":colors", $colors);
            $prep->bindParam(":subDomain", $this->subDomain);
            $prep->bindParam(":email", $email);
            $prep->bindParam(":skype", $skype);
            $prep->bindParam(":messengerType", $resolvedMessengerType);
            $prep->bindParam(":messengerUsername", $resolvedMessengerUsername);
            $prep->bindParam(":loginURL", $loginURL);
            $prep->bindParam(":landingPage", $landingPage);
            $prep->bindParam(":loginTheme", $resolvedLoginTheme);
            $prep->bindParam(":allowRegister", $resolvedAllowRegister, PDO::PARAM_BOOL);


            $prep->execute();
            $this->reloadSettings();

            return true;
        } catch (\Exception $e) {
            return false;
        }


    }


}
