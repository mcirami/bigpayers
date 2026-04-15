<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/21/2018
 * Time: 4:46 PM
 */

namespace LeadMax\TrackYourStats\Offer;


use LeadMax\TrackYourStats\User\Referrals;

class Deduction
{

    public $conversion_id;

    public $deducted_timestamp;


    public function __construct($conversion_id)
    {
        $this->conversion_id = $conversion_id;
    }

    public static function SelectOne($conversion_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM deductions WHERE conversion_id = :conversion_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $conversion_id);
        $prep->execute();

        return $prep->fetch(\PDO::FETCH_OBJ);
    }


    public function deductConversion()
    {
        if (self::doesDeductionExist($this->conversion_id)) {
            return false;
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT IGNORE INTO deductions (conversion_id) VALUES(:conversion_id)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $this->conversion_id);

        return $prep->execute();
    }


    public static function doesDeductionExist($conversion_id)
    {
        $deduction = self::SelectOne($conversion_id);
        if ($deduction) {
            return true;
        } else {
            return false;
        }
    }

    public function deductReferral()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $referralPaid = Referrals::SelectOneReferralPaidConversionId($this->conversion_id);

        if ($referralPaid == false) {
            return false;
        }

        $sql = "INSERT INTO referral_deductions (referrals_paid_id) VALUES(:referrals_paid:id)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":referrals_paid_id", $referralPaid->id);

        return $prep->execute();
    }

    // THIS ONE IS GONNA BE A DOOZY
    public function deductBonus()
    {

    }


}