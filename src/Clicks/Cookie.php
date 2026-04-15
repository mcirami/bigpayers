<?php namespace LeadMax\TrackYourStats\Clicks;


/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/13/2017
 * Time: 12:17 PM
 */
class Cookie
{

    public $affid = 0;

    public $offid = 0;

    public $cookie;

    public $companyHash = "";

    public $transferCookieAlreadySet = false;

    public function __construct($affiliate_id, $offer_id)
    {
        $this->transferCookieAlreadySet = $this->hasPreventTransferCookie();

        $this->companyHash = hash("sha256", DB_NAME);

        $this->affid = $affiliate_id;
        $this->offid = $offer_id;

        try {
            if (isset($_COOKIE[$this->companyHash])) {
                $this->cookie = json_decode($_COOKIE[$this->companyHash], true);
            } else {
                $this->cookie = array();
            }
        } catch (\Exception $e) {
            \LeadMax\TrackYourStats\LeadMax\TrackYourStats\System\Log($e, null);
            $this->deleteCookie();
        }


    }


    public function hasPreventTransferCookie()
    {
        return isset($_COOKIE["prevent_transfer"]);
    }

    public function setPreventTransferCookie()
    {
        setcookie("prevent_transfer", "1", time() + 120);
    }

    public function isUnique()
    {
        if ($this->transferCookieAlreadySet) {
            return false;
        }

        if ($this->cookie == false) {
            return true;
        }

        if (!is_array($this->cookie)) {
            $this->deleteCookie();

            return true;
        }

        if (isset($this->cookie[$this->affid])) {
            if (in_array($this->offid, $this->cookie[$this->affid])) {
                return false;
            }
        }

        return true;
    }

    public function registerClick()
    {

        if (!isset($this->cookie[$this->affid])) {
            $this->cookie[$this->affid] = array();
        }

        if (!in_array($this->offid, $this->cookie[$this->affid])) {
            $this->cookie[$this->affid][] = $this->offid;
        }

    }

    public function save()
    {
        setcookie($this->companyHash, json_encode($this->cookie), time() + (86400 * 30));
    }

    public function deleteCookie()
    {
        if (isset($_COOKIE[$this->companyHash])) {
            setcookie($this->companyHash, "", 1);

        }

    }


}