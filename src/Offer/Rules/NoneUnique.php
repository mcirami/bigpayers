<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/20/2018
 * Time: 1:25 PM
 */

namespace LeadMax\TrackYourStats\Offer\Rules;


use LeadMax\TrackYourStats\Clicks\Cookie;
use LeadMax\TrackYourStats\System\Company;

class NoneUnique implements Rule
{


    public $redirectOffer;

    private $rules = array();

    private $filteredRules = array();

    private $cookie = [];

    private $hashId;

    public function __construct($rules)
    {
        $this->rules = $rules;

        $this->filterRules();

        $this->generateHashID();

        $this->findCookie();
    }

    public function checkRules()
    {


        $offerLogCookie = new Cookie($_GET["repid"], $_GET["offerid"]);

        if ($offerLogCookie->isUnique()) {
            return true;
        }


        foreach ($this->filteredRules as $id => $rule) {

            if ($this->offerSeenRule($rule["offer_id"], $id) == false) {

                $this->logOfferRule($rule["offer_id"], $id);
                $this->saveCookie();
                $this->redirectOffer = $rule["redirect_offer"];

                return false;
            }
        }

        //they've seen all redirect rules, reset log, send to first rule
        foreach ($this->filteredRules as $id2 => $rule2) {
            $this->resetLogForOffer($rule2["offer_id"]);
            $this->logOfferRule($rule2["offer_id"], $id2);
            $this->saveCookie();
            $this->redirectOffer = $rule2["redirect_offer"];

            return false;
        }

        return true;
    }

    public function getRedirectOffer()
    {
        return $this->redirectOffer;
    }

    private function generateHashID()
    {
        $this->hashId = hash("sha256", "NON_UNIQUE_RULE_LOG");
    }

    private function findCookie()
    {
        if (isset($_COOKIE[$this->hashId])) {
            $this->cookie = json_decode($_COOKIE[$this->hashId], true);
//			// minor integrity check
//			if (is_array($this->cookie) == false)
//			{
//				$this->cookie = [];
//			}

        }
    }

    private function offerSeenRule($offer_id, $rule_id)
    {
        if (isset($this->cookie[$offer_id]) == false) {
            return false;
        }

        if (in_array($rule_id, $this->cookie[$offer_id])) {
            return true;
        }


        return false;
    }

    private function logOfferRule($offer_id, $rule_id)
    {
        if (isset($this->cookie[$offer_id]) == false) {
            $this->cookie[$offer_id] = [$rule_id];
        } else {
            $this->cookie[$offer_id][] = $rule_id;
        }

    }

    private function resetLogForOffer($offer_id)
    {
        $this->cookie[$offer_id] = [];
    }

    public function saveCookie()
    {
        setcookie($this->hashId, json_encode($this->cookie));
    }

    public function deleteCookie()
    {
        setcookie($this->hashId, "");
    }

    public function filterRules()
    {
        foreach ($this->rules as $key => $rule) {
            if ($rule["type"] == "none_unique" && $rule["is_active"] == 1) {
                if (isset($this->filteredRules[$rule["idrule"]]) == false) {
                    $id = $rule["idrule"];
                    $this->filteredRules[$id] = array(
                        "offer_id" => $rule["offer_idoffer"],
                        "redirect_offer" => $rule["redirect_offer"],
                    );
                }
            }
        }

    }


}