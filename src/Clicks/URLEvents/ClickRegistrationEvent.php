<?php

namespace LeadMax\TrackYourStats\Clicks\URLEvents;


use App\BonusOffer;
use App\User;
use Illuminate\Support\Facades\Log;
use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Clicks\Cookie;
use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\Clicks\URLProcessor;
use LeadMax\TrackYourStats\Clicks\URLTagReplacers\Base64;
use LeadMax\TrackYourStats\Clicks\URLTagReplacers\SubVariables;
use LeadMax\TrackYourStats\Clicks\URLTagReplacers\TYSVariables;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\Offer\Caps;
use LeadMax\TrackYourStats\Offer\Offer;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\Offer\Rules;
use LeadMax\TrackYourStats\System\IPBlackList;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ClickRegistrationEvent extends URLEvent
{

    public $subVarArray = [];


    public $offerId;

    public $userId;

    public $ip;

	//public $country;

    public function __construct($user_id, $offer_id, $sub_variables_array, $ip)
    {
        $this->userId = $user_id;
        $this->offerId = $offer_id;
        $this->subVarArray = $sub_variables_array;
        $this->ip = $ip;
		//$this->country = preg_replace('/[^a-zA-Z]/', '', ClickGeo::findGeo($ip))['isoCode'];
    }

    public static function getEventString(): string
    {
        return "click";
    }

    public function fire()
    {
        if ($this->registerClick()) {
            $this->sendUserToOffer();
        } else {
            return false;
        }
    }

    private function getClickType()
    {
        $blacklist = new IPBlackList($_SERVER["REMOTE_ADDR"]);

        if ($blacklist->isBlackListed()) {
            return Click::TYPE_BLACKLISTED;
        }

        $cookie = new Cookie($this->userId, $this->offerId);
        $cookie->setPreventTransferCookie();
        if ($cookie->isUnique()) {
            return Click::TYPE_UNIQUE;
        } else {
            return Click::TYPE_RAW;
        }

    }

    private function registerClick()
    {

        if ($this->validateOffer() && $this->validateUser()) {

	        // temporarily commented to check if it's causing error
           /* if (!$this->checkBonusOfferRequirementMet()) {
                return false;
            }*/

            /* if(array_key_exists("HTTP_REFERER", $_SERVER)) {
                Log::info('referer: ' . print_r($_SERVER["HTTP_REFERER"], true));
            } */
            //Log::info('ip: ' . print_r($ip, true));
            //$geo = $this->country;
            //Log::info('geo: ' . print_r($geo, true));

            //Log::info('geo: ' . print_r($_SERVER, true));
            $click = new Click();

	        $click->first_timestamp = date("Y-m-d H:i:s");
            $click->ip_address = $this->ip; //$_SERVER["REMOTE_ADDR"];
            //$click->country_code = $geo;
            $click->referer = array_key_exists("HTTP_REFERER", $_SERVER) ? $_SERVER["HTTP_REFERER"] : null;
            $click->browser_agent = $_SERVER["HTTP_USER_AGENT"];

            $click->rep_idrep = $this->userId;
            $click->offer_idoffer = $this->offerId;
            $click->click_type = $this->getClickType();

            if ($click->save()) {
                $cookie = new Cookie($this->userId, $this->offerId);
                $cookie->registerClick();
                $cookie->save();
            }

            $this->clickId = $click->id;

            if ($this->offerData->offer_type == Offer::TYPE_CPC && $click->click_type == Click::TYPE_UNIQUE) {

                $customPrice = $_GET["price"] ?? false;

                $conversion = new Conversion($click->id);

                if ($customPrice) {
                    $conversion->paid = $customPrice;
                }

                $conversion->registerSale();
            }
            return true;
        } else {
            return false;
        }
    }


    private function validateUser()
    {
        $this->getUserDataFromDatabase($this->userId);

        if ( ! $this->userData ) {
            return false;
        }

        if ($this->userData->status != 1) {
            return false;
        }


		// temporarily commented to check if it's causing error
        /*if (User::find($this->userId)->isBanned()) {
            return false;
        }*/

        if (RepHasOffer::doesAffiliateOwnOffer($this->userId, $this->offerId) == false) {
            return false;
        }

        return true;
    }

    private function validateOffer()
    {
        $this->checkIfOfferCappedAndSendToRedirectIfCapped();

        $this->getOfferDataFromDatabase($this->offerId);

        if (!is_bool($this->offerData) && $this->offerData->status) {
            if ($this->checkOfferRules()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    private function checkOfferRules()
    {
        $rules = new Rules($this->offerId, $this->ip);

        if ($rules->checkAllRules()) {
            return true;
        } else {
            return false;
        }
    }

    private function checkIfOfferCappedAndSendToRedirectIfCapped()
    {
        $caps = new Caps($this->offerId, $this->userId);

        if ($caps->isOfferCapped()) {
            $caps->sendToRedirectOffer();
        } else if ($caps->cap_rules && $caps->cap_rules['hourly_cap_status'] && $caps->isOfferCapped('hourly')) {
	        $caps->sendToRedirectOffer();
        }

	    return false;
    }

    private function sendUserToOffer()
    {
        $user_id = $this->userId;
        $this->getUserDataFromDatabase($user_id);

        $offer_id = $this->offerId;

        $this->getOfferDataFromDatabase($offer_id);
        $encodedClickId = UID::encode($this->clickId);

		$return = $this->updateClickVars($this->clickId, $encodedClickId);

        $subVarReplacer = new SubVariables($this->subVarArray);
        $tysReplacer = new TYSVariables($user_id, $this->userData->user_name, $encodedClickId, $offer_id);

        $urlProcessor = new URLProcessor($this->offerData->url);
        $urlProcessor->addTagReplacer($subVarReplacer);
        $urlProcessor->addTagReplacer($tysReplacer);
        $urlProcessor->addTagReplacer(new Base64());

        $urlProcessor->processURL();

        $urlProcessor->sendUserToUrl();
    }

	private function updateClickVars($clickId, $encodedClickId) {

		$db = DatabaseConnection::getInstance();

		//$sql = "INSERT INTO click_vars (click_id, url, sub1, sub2,sub3,sub4,sub5) VALUES ( :click_id, :url, :sub1, :sub2, :sub3, :sub4, :sub5)";
		$sql = "UPDATE click_vars set encoded = :encoded WHERE click_id = :click_id";
		$stmt = $db->prepare($sql);

		$stmt->bindParam(":click_id", $clickId);
		$stmt->bindParam(":encoded", $encodedClickId);

		return $stmt->execute();
	}

    private function checkBonusOfferRequirementMet()
    {
        $bonusOffer = BonusOffer::where('offer_id', '=', $this->offerId)->first();

        if (is_null($bonusOffer)) {
            return true;
        }


        return $bonusOffer->canAffiliateUseOffer($this->userId);
    }

}