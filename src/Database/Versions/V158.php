<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/26/2018
 * Time: 2:01 PM
 */

namespace LeadMax\TrackYourStats\Database\Versions;


use App\Privilege;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\Database\Version;
use LeadMax\TrackYourStats\Offer\Campaigns;
use LeadMax\TrackYourStats\Offer\CreateOffer;
use LeadMax\TrackYourStats\Offer\Offer;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\User\CreateUser;
use \LeadMax\TrackYourStats\User\User;

class V158 extends Version
{

    public $affiliateId = 999;
    public $managerId = 998;
    public $adminId = 997;

    public $cpaOfferId = 900;

    public function getVersion()
    {
        return 1.58;
    }

    private function createCPAOffer()
    {
        $offer = new CreateOffer();
        $offer->visibility = Offer::VISIBILITY_PRIVATE;
        $offer->created_by = 1;
        $offer->campaign_id = Campaigns::getDefaultCampaignId();
        $offer->offer_name = "TYS_TESTING_CPA";
        $offer->offer_type = Offer::TYPE_CPA;
        $offer->payout = 0;
        $offer->description = "Offer for Testing TYS Testing";
        $offer->url = "#clickid#";
        $offer->save();

        return Offer::updateOfferId($offer->idoffer, $this->cpaOfferId);
    }

    private function createAffiliate()
    {
        $user = new CreateUser();
        $user->user_name = "default";
        $user->email = "null@example.com";
        $user->first_name = "TYS";
        $user->last_name = "TESTER";
        $user->userType = Privilege::ROLE_AFFILIATE;
        $user->referrer_repid = $this->managerId;
        $user->password = "jv001133";

        if ($user->save()) {
            return User::updateUserId($user->idrep, $this->affiliateId);
        } else {
            return false;
        }
    }


    private function createDefaultManager()
    {
        $user = new CreateUser();
        $user->user_name = "DefaultManager";
        $user->email = "defaultManager@example.com";
        $user->first_name = "TYS";
        $user->last_name = "TESTER";
        $user->userType = Privilege::ROLE_MANAGER;
        $user->referrer_repid = $this->adminId;
        $user->password = "jv001133";
        if ($user->save()) {
            return User::updateUserId($user->idrep, $this->managerId);
        } else {
            return false;
        }
    }

    private function createDefaultAdmin()
    {
        $user = new CreateUser();
        $user->user_name = "DefaultAdmin";
        $user->email = "defaultAdmin@example.com";
        $user->first_name = "TYS";
        $user->last_name = "TESTER";
        $user->userType = Privilege::ROLE_ADMIN;
        $user->referrer_repid = 1;
        $user->password = "jv001133";
        if ($user->save()) {
            return User::updateUserId($user->idrep, $this->adminId);
        } else {
            return false;
        }
    }

    public function update()
    {
        DatabaseConnection::changeConnection($this->getDB());


        $success =
            $this->createCPAOffer() &&
            $this->createDefaultAdmin() &&
            $this->createDefaultManager() &&
            $this->createAffiliate() &&
            RepHasOffer::assignAffiliateToOffer($this->cpaOfferId, $this->affiliateId);

        return $success;
    }


    public function verifyUpdate(): bool
    {
        $db = $this->getDB();
        $hasOffer = ($db->query("SELECT idoffer FROM offer WHERE idoffer = {$this->cpaOfferId}")->rowCount() > 0);

        $hasAffiliate = ($db->query("SELECT idrep FROM rep WHERE idrep = {$this->affiliateId}")->rowCount() > 0);

        return ($hasAffiliate && $hasOffer);
    }

}