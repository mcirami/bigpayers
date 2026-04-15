<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/20/2018
 * Time: 12:21 PM
 */

namespace LeadMax\TrackYourStats\User;


use LeadMax\TrackYourStats\Clicks\Conversion;

class ReferralRegister
{

    public $userId;

    public $myReferralStructure;

    public $paid;

    public $conversionId;

    private $conversionData;

    public function __construct($userId = null)
    {
        if ($userId !== null && is_numeric($userId)) {
            $this->userId = $userId;
            $this->getMyReferralStructure();
        }
    }

    public function getMyReferralStructure()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM referrals WHERE aff_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $this->userId);
        $prep->execute();

        $this->myReferralStructure = $prep->fetch(\PDO::FETCH_OBJ);
    }

    public function registerCommission($conversionId)
    {
        if ($this->checkAndGetReferralStructure() == false) {
            return false;
        }

        if ($this->verifyReferralActive() == false) {
            return false;
        }

        $this->conversionId = $conversionId;
        $this->getConversionData();
        if ($this->verifyConversion() == false) {
            return false;
        }

        $this->paid = $this->calculateCommissionEarned($this->conversionData->paid);


        return $this->saveCommission();
    }

    public function saveCommission()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO referrals_paid (aff_id, referred_aff_id, conversion_id, paid, timestamp) VALUES (:aff_id, :referred_aff_id, :conversion_id, :paid, :timestamp)";
        $prep = $db->prepare($sql);

        $today = date("Y-m-d H:i:s");

        $prep->bindParam(":aff_id", $this->userId); //my affiliate id
        $prep->bindParam(":referred_aff_id", $this->myReferralStructure->referrer_user_id); // my referred user id
        $prep->bindParam(":conversion_id", $this->conversionId);
        $prep->bindParam(":paid", $this->paid); // my referred user paid
        $prep->bindParam(":timestamp", $today);

        return $prep->execute();
    }


    public function verifyConversion()
    {

        if (isset($this->conversionData) == false) {
            return false;
        }

        if ($this->conversionData == false) {
            return false;
        }


        return true;
    }

    public function getConversionData()
    {
        $this->conversionData = Conversion::selectOneByConversionId($this->conversionId)->fetch(\PDO::FETCH_OBJ);
    }

    public function calculateCommissionEarned($paid)
    {
        switch ($this->myReferralStructure->referral_type) {
            case 'percentage':
                $commissionPaid = ($this->myReferralStructure->payout / 100) * $paid;
                break;
            case 'flat':
                $commissionPaid = $this->myReferralStructure->payout;
                break;
            default:
                return false;
        }

        return $commissionPaid;
    }

    public function verifyReferralActive()
    {
        $today = date("Y-m-d");

        // If the referrer is outside the date range or is not marked as active
        if ($today < $this->myReferralStructure->start_date ||
            $today > $this->myReferralStructure->end_date ||
            $this->myReferralStructure->is_active != 1) {
            return false;
        }

        return true;
    }

    public function checkAndGetReferralStructure()
    {
        if (isset($this->userId)) {
            if (isset($this->myReferralStructure) == false) {
                $this->getMyReferralStructure();
            }

            if ($this->myReferralStructure !== false) {
                return true;
            }

            return false;
        } else {
            return false;
        }
    }


}