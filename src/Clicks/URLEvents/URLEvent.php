<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/28/2018
 * Time: 12:20 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents;


use App\Support\LegacyClick as Click;
use App\Support\ClickVariables as ClickVars;
use App\Support\ClickIdCodec as UID;
use App\Support\Tracking\URLProcessor;
use App\Support\Tracking\URLTagReplacers\Base64;
use App\Support\Tracking\URLTagReplacers\SubVariables;
use App\Support\Tracking\URLTagReplacers\TYSVariables;
use App\Support\LegacyOffer as Offer;
use App\Support\LegacyUser as User;


abstract class URLEvent
{

    public $clickId;

    protected $userData;

    protected $offerData;

    protected $clickData;

    public $clickSubVarsArray;

//	public function __construct($click_id)
//	{
//		$this->clickId = $click_id;
//	}

    abstract function fire();

    abstract static function getEventString(): string;

    protected function setUpURLProcessorWithDBData($url)
    {
        $encodedClickId = UID::encode($this->clickId);

        $offer_id = $this->offerData->idoffer;

        $subVarReplacer = new SubVariables($this->clickSubVarsArray);
        $tysReplacer = new TYSVariables($this->userData->idrep, $this->userData->user_name, $encodedClickId, $offer_id);


        $urlProcessor = new URLProcessor($url);
        $urlProcessor->addTagReplacer($subVarReplacer);
        $urlProcessor->addTagReplacer($tysReplacer);
        $urlProcessor->addTagReplacer(new Base64());

        return $urlProcessor;
    }

    protected function getAllDataFromDatabase()
    {
        $this->getClickDataFromDatabase($this->clickId);
        $this->getClickSubVarsArrayFromDatabase($this->clickId);

        $this->getOfferDataFromDatabase($this->clickData->offer_idoffer);

        $this->getUserDataFromDatabase($this->clickData->rep_idrep);
    }

    protected function getUserDataFromDatabase($user_id)
    {
        $this->userData = User::SelectOne($user_id);
    }

    protected function getOfferDataFromDatabase($offer_id)
    {
        $this->offerData = Offer::selectOneQuery($offer_id)->fetch(\PDO::FETCH_OBJ);
    }

    protected function getClickDataFromDatabase($click_id)
    {
        $this->clickData = Click::SelectOne($click_id);
    }

    protected function getClickSubVarsArrayFromDatabase($click_id)
    {
        $this->clickSubVarsArray = ClickVars::getSubVarArray($click_id);
    }

}
