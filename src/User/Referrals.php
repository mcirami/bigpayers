<?php namespace LeadMax\TrackYourStats\User;

use LeadMax\TrackYourStats\System\Session;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/29/2017
 * Time: 5:01 PM
 */

use PDO;

class Referrals
{
    /*
     DB COLUMNS:
     ----------------------------------------------------
        referrer_user_id
        aff_id	start_date
        end_date
        referral_type (Flat Fee or Percentage)
        commission_basis
        min_payment_threshhold
        payout (Amount or Percentage of Commission)
     ----------------------------------------------------
    */


    public $affid = -1;


    public $referrals = array();

    public $selectedAffiliate;

    public $myReferrerStructure = false;


    public function __construct($affid, $findMyReferrerStructure = false)
    {
        $this->affid = $affid;

        if (!$findMyReferrerStructure) {
            $this->referrals = $this->queryGetAffiliateReferrals()->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->myReferrerStructure = $this->findMyReferrerStructure($affid)->fetch(PDO::FETCH_OBJ);
        }

        $this->selectedAffiliate = User::SelectOne($affid);
    }

    public static function SelectOneReferralPaidConversionId($conversion_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM referrals_paid WHERE conversion_id = :conversion_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":conversion_id", $conversion_id);
        $prep->execute();

        return $prep->fetch(PDO::FETCH_OBJ);
    }

    public static function deleteReferralStructure($referringUserId, $affiliateId)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DELETE FROM referrals WHERE referrer_user_id = :refUserId AND aff_id = :aff_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":refUserId", $referringUserId);
        $prep->bindParam(":aff_id", $affiliateId);

        return $prep->execute();
    }


    public function logRegistrationEvent($conversionId, $userId)
    {
        $logFile = fopen(__DIR__."/../../referral_log.txt", "a");

        $logLine = date("Y-m-d H:i:s")."|| CONVERSION_ID: {$conversionId} || USER_ID: {$userId} ".PHP_EOL.PHP_EOL;

        fwrite($logFile, $logLine);

        fclose($logFile);
    }


    public function logFunctionArgs($args)
    {
        $logFile = fopen(__DIR__."/../../referral_log_func_args.txt", "a");

        $logLine = date("Y-m-d H:i:s")."|| {$args} ||".PHP_EOL.PHP_EOL;

        fwrite($logFile, $logLine);

        fclose($logFile);
    }

//@deprecated
    public function registerCommission($paid)
    {
//		$this->logFunctionArgs(func_get_arg(0));
//
//		\LeadMax\TrackYourStats\System\Log(func_get_arg(0), $this);

        $structure = $this->myReferrerStructure;
        // Commission requires a Referrer Structure
        if (!$structure) {
            return false;
        }
        $today = date("Y-m-d");


        switch ($structure->referral_type) {
            case 'percentage':
                $commissionPaid = ($structure->payout / 100) * $paid;
                break;
            case 'flat':
                $commissionPaid = $structure->payout;
                break;
            default:
                return false;
        }
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO referrals_paid (aff_id, referred_aff_id, paid) VALUES (:aff_id, :referred_aff_id, :paid)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":aff_id", $this->affid); //my affiliate id
        $prep->bindParam(":referred_aff_id", $structure->referrer_user_id); // my referred user id
        $prep->bindParam(":paid", $commissionPaid); // my referred user paid

        return $prep->execute();
    }


    private function findMyReferrerStructure($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM referrals WHERE aff_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);
        $prep->execute();

        return $prep;
    }

    public static function findReferrer($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT referrer_user_id FROM referrals WHERE aff_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":user_id", $user_id);

        if ($prep->execute()) {
            return $prep->fetch(PDO::FETCH_ASSOC)["referrer_user_id"] ?? '';
        }

        return false;
    }

    public static function updateReferrer($user_id, $referrer_user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE referrals SET referrer_user_id = :referrer_user_id WHERE aff_id = :user_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":referrer_user_id", $referrer_user_id);
        $prep->bindParam(":user_id", $user_id);

        return $prep->execute();
    }

    public static function printSelectBoxForEditAffiliate($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep INNER JOIN privileges ON privileges.is_rep = 1 AND privileges.rep_idrep = rep.idrep WHERE rep.lft > :left AND rep.rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);

        if ($prep->execute()) {
            $referrer_user_id = self::findReferrer($user_id);

            if ($referrer_user_id != null) {


                echo " <p>
                         <label class='value_span9'>My Referrer</label>
                       <select name=\"referrer_box\">
                      
                    
                      ";

                $result = $prep->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result as &$user) {
                    if ($user["idrep"] == $referrer_user_id) {
                        echo "<option selected value=\"{$user["idrep"]}\">{$user["user_name"]}</option>";
                    } else {
                        echo "<option  value=\"{$user["idrep"]}\">{$user["user_name"]}</option>";
                    }
                }
                echo "  </select>
                    </p>";
            }
        }

        return false;

    }

    public static function selectNoneAssignedReferralAffiliates($affiliate_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idrep, user_name FROM rep
 INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_rep = 1

 WHERE rep.idrep NOT IN (SELECT aff_id FROM referrals  ) AND rep.lft > :left AND rep.rgt < :right AND rep.idrep != :affiliate_id2 GROUP BY rep.idrep";
        $prep = $db->prepare($sql);

        $userData = unserialize($_SESSION["userData"]);

        $prep->bindParam(":left", $userData->lft);
        $prep->bindParam(":right", $userData->rgt);
//        $prep->bindParam(":affiliate_id", $affiliate_id);
        $prep->bindParam(":affiliate_id2", $affiliate_id);


        $prep->execute();

        return $prep;
    }

    public function hasReferrals()
    {
        if (empty($this->referrals)) {
            return false;
        }

        return true;
    }

    public function dumpReferralsToJavascript()
    {
        if (empty($this->referrals)) {
            return false;
        }

        echo "<script type=\"text/javascript\">";
        echo "var refs = [];";
        foreach ($this->referrals as $key => $row) {
            echo "refs[{$row["aff_id"]}] = ".json_encode($row).";";
        }
        echo "</script>";
    }

    public function printReferrersToTable()
    {
        if (empty($this->referrals)) {
            return false;
        }

        echo "
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Referral Type</th>
                    <th>Commission Basis</th>
                    <th>Amount / Percentage</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
        ";

        foreach ($this->referrals as $key => $row) {
            echo "<tr id='{$row["referrer_user_id"]}-{$row["aff_id"]}'>";
            echo "<td>{$row["user_name"]}</td>";
            echo "<td>{$row["start_date"]}</td>";
            if ($row["end_date"] == "") {
                echo "<td>Indefinite</td>";
            } else {
                echo "<td>{$row["end_date"]}</td>";
            }
            echo "<td>{$row["referral_type"]}</td>";
            echo "<td>{$row["commission_basis"]}</td>";
            echo "<td>{$row["payout"]}</td>";
            echo "<td>{$row["is_active"]}</td>";
            echo "<td><a href='#' onclick='loadRef({$row["referrer_user_id"]}, {$row["aff_id"]});' class='btn btn-default btn-sm'><img src='/images/icons/pencil.png'></a><a href='#' onclick='deleteReferral({$row["aff_id"]});' class='btn btn-default btn-sm'><img src='/images/icons/cancel.png'></a>";

            echo "</tr>";
        }


    }

    private function queryGetAffiliateReferrals()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT referrer_user_id, aff_id, start_date, end_date, referral_type, commission_basis, min_payment_threshhold, payout, is_active, user_name FROM referrals
                LEFT JOIN rep ON rep.idrep = referrals.aff_id
                WHERE referrer_user_id = :affid";

        $prep = $db->prepare($sql);
        $prep->bindParam(":affid", $this->affid);
        $prep->execute();

        return $prep;
    }


    static function updateReferral($referrerAffID, $affiliateID, $options)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE referrals SET start_date = :start_date, end_date = :end_date,
                referral_type = :referral_type, payout = :amount, is_active = :is_active
                WHERE referrer_user_id = :referrerAffID AND aff_id = :affID";

        $prep = $db->prepare($sql);

        if ($options["end_date"] == "") {
            $options["end_date"] = "3000-01-01";
        }

        if ($options["is_active"] == "unactive") {
            $options["is_active"] = 0;
        } else {
            $options["is_active"] = 1;
        }


        $prep->bindParam(":referrerAffID", $referrerAffID);
        $prep->bindParam(":affID", $affiliateID);
        $prep->bindParam(":start_date", $options["start_date"]);
        $prep->bindParam(":end_date", $options["end_date"]);
        $prep->bindParam(":referral_type", $options["referral_type"]);
        $prep->bindParam(":amount", $options["amount"]);
        $prep->bindParam(":is_active", $options["is_active"]);

        return $prep->execute();
    }


    static function addReferral($referrerAffID, $affiliateID, $options)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO referrals (referrer_user_id, aff_id, start_date, end_date, referral_type, payout) VALUES (:referrerAffID, :affID, :start_date, :end_date, :referral_type, :payout)";
        $prep = $db->prepare($sql);

        if ($options["end_date"] == "") {
            $options["end_date"] = "3000-01-01";
        }

        $prep->bindParam(":referrerAffID", $referrerAffID);
        $prep->bindParam(":affID", $affiliateID);
        $prep->bindParam(":start_date", $options["start_date"]);
        $prep->bindParam(":end_date", $options["end_date"]);
        $prep->bindParam(":referral_type", $options["referral_type"]);
        $prep->bindParam(":payout", $options["payout"]);

        return $prep->execute();
    }

    static function printAffiliatesToSelectBox()
    {
        $result = User::selectAllOwnedAffiliates()->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $val) {

            echo "<option value='{$val["idrep"]}'>{$val["user_name"]}</option>";


        }

    }

}