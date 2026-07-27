<?php

namespace App\Support\Tracking;

use App\Support\NativeRequest;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/13/2017
 * Time: 12:17 PM
 */
class ClickCookie
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
            if (NativeRequest::hasCookie($this->companyHash)) {
                $this->cookie = json_decode(NativeRequest::cookie($this->companyHash), true);
            } else {
                $this->cookie = array();
            }
        } catch (\Exception $e) {
            \Log($e, null);
            $this->deleteCookie();
        }


    }


    public function hasPreventTransferCookie()
    {
        return NativeRequest::hasCookie('prevent_transfer');
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
        if (NativeRequest::hasCookie($this->companyHash)) {
            setcookie($this->companyHash, "", 1);

        }

    }


}
