<?php namespace LeadMax\TrackYourStats\Report\Repositories;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/23/2017
 * Time: 11:25 AM
 */
class ReferralRepository
{


    public function __construct()
    {

    }

    public function getAffiliateReferrals($left, $right, $startDate, $endDate, $selectActiveOnly = true)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT referrer_user_id, aff_id, start_date, end_date, referral_type, commission_basis, payout FROM referrals INNER JOIN rep ON rep.idrep = referrer_user_id AND rep.lft > :left AND rep.rgt < :right
                WHERE :startDate >= referrals.start_date   AND  :endDate <= referrals.end_date ";
        if ($selectActiveOnly) {
            $sql .= " AND referrals.is_active = 1";
        }

        $prep = $db->prepare($sql);
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->bindParam(":startDate", $startDate);
        $prep->bindParam(":endDate", $endDate);

        $prep->execute();

        return $prep;
    }

}