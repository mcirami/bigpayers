<?php
/*
* =======================================================================
* CLASSNAME:        rep_has_offer
* DATE CREATED:      16-05-2017
* FOR TABLE:          rep_has_offer
* FOR DATA BASE:    trackyourstats
* IMPORTANT:
* the 'sanitize()' keyword is a defined function to prevent sql injection located @ lib/Functions.php
* 'post()' is also defined function located @ lib/funtions.php
* You can further improve these functions if necessary.
* =======================================================================
*/

namespace LeadMax\TrackYourStats\Offer;

use App\BonusOffer;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\System\Notifications;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;
use LeadMax\TrackYourStats\User\Tree;
use \LeadMax\TrackYourStats\User\User;
use PDO;


//Begin class
class RepHasOffer
{
    public $idrep_has_offer;
    public $rep_idrep;
    public $offer_idoffer;
    public $lastOfferId;
    // Table Columns
    //(idrep_has_offer,rep_idrep,offer_idoffer)
    // Table Prepare Columns
    //(:idrep_has_offer,:rep_idrep,:offer_idoffer)

    //Constructor
    public function __construct(
        $idrep_has_offer = '',
        $rep_idrep = '',
        $offer_idoffer = ''
    ) {
        $this->idrep_has_offer = $idrep_has_offer;
        $this->rep_idrep = $rep_idrep;
        $this->offer_idoffer = $offer_idoffer;
    }

    public static function requestOffer($offer_id, $user_id)
    {

        $offer = Offer::selectOneQuery($offer_id)->fetch(\PDO::FETCH_OBJ);

        $user = User::SelectOne($user_id);

        $title = "{$user->user_name} Offer Request";
        $body = "User {$user->user_name} has request access to offer $offer->offer_name. <br/>To assign them to this offer click <a href='approve_offer_request.php?id={$offer_id}&u={$user_id}'>here</a>. </br>
        This is an automated message. If you have any questions please ask an Administrator.
";


        $toSend = array();

        if (Session::userType() == \App\Privilege::ROLE_AFFILIATE) {
            $managerPermission = new Permissions($user->referrer_repid);
            if ($managerPermission->can("approve_offer_requests")) {
                $toSend[] = $user->referrer_repid;
            }


            $manager = User::SelectOne($user->referrer_repid);

            $adminPermission = new Permissions($manager->referrer_repid);
            if ($adminPermission->can("approve_offer_requests")) {
                $toSend[] = $manager->referrer_repid;
            }


        }

        if (Session::userType() == \App\Privilege::ROLE_MANAGER) {
            $adminPermission = new Permissions($user->referrer_repid);
            if ($adminPermission->can("approve_offer_requests")) {
                $toSend[] = $user->referrer_repid;
            }
        }

        $toSend[] = 1;


        if ($user && $offer) {
            return Notifications::sendNotification($toSend, 1, $title, $body);
        } else {
            return false;
        }

    }

    public static function assignAffiliateToOffer($offer_id, $user_id)
    {

        $offer = Offer::selectOneQuery($offer_id)->fetch(\PDO::FETCH_OBJ);


        if (!$offer) {
            return false;
        }

        $db = DatabaseConnection::getInstance();
        $sql = "INSERT INTO rep_has_offer (offer_idoffer, rep_idrep, payout) VALUES(:offer_id, :user_id, :payout)";

        $prep = $db->prepare($sql);
        $prep->bindParam(":offer_id", $offer_id);
        $prep->bindParam(":user_id", $user_id);
        $prep->bindParam(":payout", $offer->payout);


        return $prep->execute();
    }

    static function assignPostBackToAffiliatesOffers($postBackURL, $user_id, array $offerIDs)
    {
        if (empty($offerIDs) == true) {
            return false;
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep_has_offer SET postback_url = ? WHERE rep_idrep = ? AND rep_has_offer.offer_idoffer IN (";


        $queryValues = [$postBackURL, $user_id];


        for ($i = 0; $i < count($offerIDs); $i++) {
            if ($i == (count($offerIDs) - 1)) {
                $sql .= "?)";
            } else {
                $sql .= "?,";
            }

            $queryValues[] = $offerIDs[$i];
        }


        $prep = $db->prepare($sql);


        return $prep->execute($queryValues);
    }

    static function find_offers_for_logged_in_user()
    {
        Global $userData;
        Global $userID;
        Global $userType;

        switch ($userType) {
            case \App\Privilege::ROLE_GOD:
                $sql = "SELECT idoffer, offer_name FROM offer";
                break;

            case \App\Privilege::ROLE_ADMIN:
                $sql = "SELECT idoffer, offer_name FROM offer";
                break;

            case \App\Privilege::ROLE_MANAGER:

                break;
        }

    }

    public static function findUsersAffiliates($userID)
    {
        $userData = User::SelectOne($userID);

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN privileges ON rep.idrep = privileges.rep_idrep AND privileges.is_rep =1 WHERE rep.lft > :left AND rep.rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":left", $userData->lft);
        $prep->bindParam(":right", $userData->rgt);

        $prep->execute();

        return $prep;
    }

    public static function massAssignUsers($userList, $offerList, $usersType)
    {
        if ($usersType !== \App\Privilege::ROLE_AFFILIATE) {
            $newList = array();
            foreach ($userList as $user) {
                $repList = self::findUsersAffiliates($user);
                foreach ($repList as $rep) {
                    $newList[] = $rep["idrep"];
                }

            }
            $userList = $newList;
        }
        self::massAssignAffiliates($userList, $offerList);
    }

    public static function massUpdateOfferPayouts(array $offerList)
    {
        foreach ($offerList as $offerId) {
            self::updateOfferPayoutForAffiliates($offerId);
        }
    }

    public static function updateOfferPayoutForAffiliates($offer_id)
    {
        $offer = Offer::selectOneQuery($offer_id)->fetch(PDO::FETCH_ASSOC);
        if ($offer["parent"] != null) {
            return false;
        }
        $offerPayout = $offer["payout"];
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep_has_offer SET payout = :payout WHERE offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":offer_id", $offer_id);
        $prep->bindParam(":payout", $offerPayout);

        return $prep->execute();
    }

    public static function massAssignAffiliates($userList, $offerList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        foreach ($offerList as $offerID) {
            $defaultOfferPayout = Offer::selectOneQuery($offerID)->fetch(PDO::FETCH_ASSOC)["payout"];
            $sql = "INSERT IGNORE INTO rep_has_offer (rep_idrep, offer_idoffer, payout) VALUES";
            $questionMarks = [];
            $insertValues = [];
            foreach ($userList as $userID) {
                $questionMarks[] = "(?,?,?)";
                $insertValues[] = $userID;
                $insertValues[] = $offerID;
                $insertValues[] = $defaultOfferPayout;
            }

            $sql .= implode(",", $questionMarks);
            $prep = $db->prepare($sql);
            $prep->execute($insertValues);

        }

        return true;
    }


    public static function queryGetAffiliatesAssignedToOffer($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep INNER JOIN rep_has_offer ON rep_has_offer.rep_idrep = rep.idrep AND rep_has_offer.offer_idoffer = :id WHERE rep.lft > :left AND rep.rgt < :right";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->bindParam(":left", Session::userData()->lft);
        $prep->bindParam(":right", Session::userData()->rgt);
        $prep->execute();

        return $prep;
    }


    public static function unAssignAffiliates($userList, $offerId)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DELETE FROM rep_has_offer WHERE offer_idoffer = ? AND rep_idrep IN (";

        $queryValues = [$offerId];

        for ($i = 0; $i <= count($userList) - 1; $i++) {

            if ($i !== count($userList) - 1) {
                $sql .= "?,";
            } else {
                $sql .= "?)";
            }


            $queryValues[] = $userList[$i];
        }


        $prep = $db->prepare($sql);

        return $prep->execute($queryValues);
    }


    static function noneRepOwnOffer($id, $userID)
    {
        //TODO: Does this effect the system ?
        if (Session::userType() == \App\Privilege::ROLE_GOD || Session::userType() == \App\Privilege::ROLE_ADMIN) {
            return true;
        }

        $sql = "SELECT DISTINCT
                        offer.idoffer,
                        offer.offer_name,
                        offer.description,
                        offer.url,
                        offer.payout,
                        offer.status,
                        offer.offer_timestamp
                    FROM
                        offer
                    INNER JOIN rep
                    ON
                    rep.lft > :left AND rep.rgt < :right
                    INNER JOIN
                        rep_has_offer
                    ON
                       rep_has_offer.rep_idrep = rep.idrep AND  rep_has_offer.offer_idoffer = :id OR offer.created_by = :repID
                       
                        ";
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $prep = $db->prepare($sql);

        $prep->bindParam(":id", $id);

        $lr = Tree::getLR($userID);
        $prep->bindParam(":left", $lr["lft"]);
        $prep->bindParam(":right", $lr["rgt"]);

        $prep->bindParam(":repID", $userID);

        $prep->execute();

        if ($prep->rowCount() > 0) {
            return true;
        }

        return false;


    }

    static function getPostbackURL($offid, $affid)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT  postback_url FROM rep_has_offer WHERE rep_idrep = :affid AND
        offer_idoffer = :offid ";

        $prep = $db->prepare($sql);

        $prep->bindParam(":offid", $offid);
        $prep->bindParam(":affid", $affid);

        $prep->execute();

        $result = $prep->fetch(PDO::FETCH_NUM);

        if ($result == false) {
            return "";
        }

        if (is_array($result) && empty($result)) {
            return "";
        }

        return $result[0];
    }

    static function updatePostbackUrl($offid)
    {
        $url = post("postback_url");


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE rep_has_offer SET postback_url = :postback_url ";


        $sql .= " WHERE rep_idrep = :repid AND offer_idoffer = :offid";

        $prep = $db->prepare($sql);

        $prep->bindParam(":postback_url", $url);


        $userID = System\Session::userID();
        $prep->bindParam(":repid", $userID);

        $prep->bindParam(":offid", $offid);


        if ($prep->execute()) {
            return true;
        } else {
            Log("failed to update offer url in rep_has_offer, repid = {$_SESSION["repid"]}", null);

            return false;
        }


    }

    public static function doesAffiliateOwnOffer($userId, $offerId)
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep_has_offer WHERE rep_idrep = :user_id AND offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);

        $prep->bindParam(":user_id", $userId);
        $prep->bindParam(":offer_id", $offerId);

        $prep->execute();


        return ($prep->rowCount() > 0);
    }


    //descendants = (right -  left - 1) / 2


    public function selectManagers($offid)
    {
        // get all affiliates assigned to this offer
        $affs = $this->selectReferrerIDsFromRepsWith($offid)->fetchAll(PDO::FETCH_OBJ);

        // new array to hold managers ids (referrer_repid)
        $managerIDs = array();

        //gets all manager ids
        //if affiliate's referrer id is not in manager id array, then add
        foreach ($affs as $aff) {
            if (!in_array($aff->referrer_repid, $managerIDs)) {
                $managerIDs[] = $aff->referrer_repid;
            }
        }


        $newIDs = array();

        $managerRepCount = array();


        for ($i = 0; $i < count($managerIDs); $i++) {
            // get manager's left and right on tree
            $LR = Tree::getLR($managerIDs[$i]);

            // count of affiliates to this manager
            $childCount = 0;

            // if the affiliate is assigned to current manager
            foreach ($affs as $aff) {
                if ($aff->referrer_repid == $managerIDs[$i]) {
                    $childCount++;
                }
            }


            // if an affiliate is assign to this manager
            if ($childCount < Tree::findChildren($LR["lft"], $LR["rgt"])) {
                // manager has an affiliate assigned to this offer
                $newIDs[] = $managerIDs[$i];

                $managerRepCount[$managerIDs[$i]] = $childCount." Affiliate(s) assigned ";

            }
        }


        $userObj = new User();
        $selectAllManagers = $userObj->select_all_managers_num()->fetchAll(PDO::FETCH_ASSOC);

        if (count($newIDs) != 0) {
            // gets all managers
            $sql = "SELECT * FROM rep WHERE idrep = ?";
            for ($i = 1; $i < count($newIDs); $i++) {
                $sql .= " OR idrep = ? ";
            }


            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


            $prep = $db->prepare($sql);
            $prep->execute($newIDs);
            $result = $prep->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $key => $val) {
                $result[$key]['user_name'] = $result[$key]['user_name']." - ".$managerRepCount[$val['idrep']];
            }

            foreach ($selectAllManagers as $key => $val) {
                foreach ($result as $key2 => $val2) {
                    if ($val2['idrep'] == $val['idrep']) {
                        unset($selectAllManagers[$key]);
                    }
                }

            }

            $result = array_merge($result, $selectAllManagers);

        } else {
            $result = $selectAllManagers;
        }


        return $result;
    }

    public function selectAllAssignedManagers($offid)
    {
        // get all affiliates assigned to this offer
        $affs = $this->selectReferrerIDsFromRepsWith($offid)->fetchAll(PDO::FETCH_OBJ);

        // new array to hold managers ids (referrer_repid)
        $managerIDs = array();

        //gets all manager ids
        //if affiliate's referrer id is not in manager id array, then add
        foreach ($affs as $aff) {
            if (!in_array($aff->referrer_repid, $managerIDs)) {
                $managerIDs[] = $aff->referrer_repid;
            }
        }


        $newIDs = array();

        $managerRepCount = array();


        for ($i = 0; $i < count($managerIDs); $i++) {
            // get manager's left and right on tree
            $LR = Tree::getLR($managerIDs[$i]);

            // count of affiliates to this manager
            $childCount = 0;

            // if the affiliate is assigned to current manager
            foreach ($affs as $aff) {
                if ($aff->referrer_repid == $managerIDs[$i]) {
                    $childCount++;
                }
            }


            // if an affiliate is assign to this manager
            if ($childCount == Tree::findChildren($LR["lft"], $LR["rgt"])) {
                // manager has an affiliate assigned to this offer
                $newIDs[] = $managerIDs[$i];

                $managerRepCount[$managerIDs[$i]] = $childCount." Affiliate(s) assigned (All)";
            }
        }


        if (count($newIDs) == 0) {
            return false;
        }

        // gets all managers
        $sql = "SELECT * FROM rep WHERE idrep = ?";
        for ($i = 1; $i < count($newIDs); $i++) {
            $sql .= " OR idrep = ? ";
        }


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();


        $prep = $db->prepare($sql);
        $prep->execute($newIDs);

//        dd($prep->fetchAll(PDO::FETCH_ASSOC));


        $result = $prep->fetchAll(PDO::FETCH_OBJ);


        foreach ($result as $key => $val) {
            $result[$key]->user_name = $result[$key]->user_name." - ".$managerRepCount[$val->idrep];
        }

        return $result;


    }


    // SELECT All FROM REP HAS OFFER WHERE REP IS ASSIGNED TO OFFER
    public function selectReferrerIDsFromRepsWith($idoffer)
    {

        //$idoffer = get("idoffer");
//        echo $idoffer;
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT rep_has_offer.rep_idrep, rep.referrer_repid FROM rep_has_offer INNER JOIN rep ON rep.idrep = rep_has_offer.rep_idrep WHERE offer_idoffer = :idoffer ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':idoffer', $idoffer);
        $stmt->execute();

        return $stmt;
        // print_x($stmt->fetchALL(PDO::FETCH_OBJ));
    }


    // SELECT All FROM REP HAS OFFER WHERE REP IS ASSIGNED TO OFFER
    public function selectAllAssignedReps($idoffer)
    {

        //$idoffer = get("idoffer");
//        echo $idoffer;
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep_has_offer WHERE offer_idoffer = :idoffer";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':idoffer', $idoffer);
        $stmt->execute();

        return $stmt;
        // print_x($stmt->fetchALL(PDO::FETCH_OBJ));
    }

    // SELECT All FROM REP HAS OFFER WHERE REP IS ASSIGNED TO OFFER
    public function selectAllAssignedManagerAffiliates($idoffer, $managerID)
    {

        //$idoffer = get("idoffer");
//        echo $idoffer;
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep_has_offer INNER JOIN rep ON rep.idrep = rep_has_offer.rep_idrep AND rep.referrer_repid = :managerID WHERE offer_idoffer = :idoffer";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':idoffer', $idoffer);
        $stmt->bindParam(":managerID", $managerID);
        $stmt->execute();

        return $stmt;
        // print_x($stmt->fetchALL(PDO::FETCH_OBJ));
    }


    // SELECT ONE
    public function SelectOne($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep_has_offer WHERE idrep_has_offer=:id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }


    // DELETE ALL THAT MATCH OFFER ID
    public function DeleteAll($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "DELETE FROM `rep_has_offer` WHERE `offer_idoffer` = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

    }


    public function CreateOfferWithManagerAssign($redirect_to)
    {
        $submit = post("button");
        if ($submit) {

            if (!empty($_POST["replist"])) {
                $managerList = $_POST["replist"];
                $managerIDList = array();

                $newID = array();


                $sql = "";

                for ($i = 0; $i < count($managerList); $i++) {
                    if ($i == 0) {
                        $sql .= "SELECT idrep FROM rep WHERE referrer_repid = ? ";
                    } else {
                        $sql .= " OR ? ";
                    }

                    $managerIDList[] = $managerList[$i];
                }

                $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
                $prep = $db->prepare($sql);

                $prep->execute($managerIDList);

                $repIDlist = $prep->fetchAll(PDO::FETCH_NUM);


                for ($i = 0; $i < count($repIDlist); $i++) {
                    $newID[] = $repIDlist[$i][0];
                }
                $_POST["replist"] = $newID;

            }


            $this->CreateOfferWithRepHasOffer($redirect_to);


        }

    }

    public static function assignAffiliateToPublicOffers($user_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT idoffer, payout FROM offer WHERE is_public = 1";
        $prep = $db->prepare($sql);
        $prep->execute();

        $publicOffers = $prep->fetchAll(PDO::FETCH_OBJ);

        if (empty($publicOffers)) {
            return;
        }

        $insertValues = [];
        $questionMarks = [];

        foreach ($publicOffers as $offer) {
            $questionMarks[] = "(?,?,?)";

            $insertValues[] = $offer->idoffer;
            $insertValues[] = $offer->payout;
            $insertValues[] = $user_id;
        }

        $sql = "INSERT IGNORE INTO rep_has_offer (offer_idoffer, payout, rep_idrep) VALUES".implode(",",
                $questionMarks);

        $prep = $db->prepare($sql);

        return $prep->execute($insertValues);
    }


    // INSERT OFFER WITH AFF
    public function CreateOfferWithRepHasOffer($redirect_to)
    {
        $submit = post('button');

        if ($submit) {

            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $db->beginTransaction();

            try {
				$offer_name = trim(preg_replace('/\\s+/', ' ', post('offer_name')));
	            $offer_name = preg_replace('/[^\p{L}\p{N}\s.,!@#$%^&*()\-_=+\[\]{}:;\'"<>?\/|]/u', '', $offer_name);
                $description = post('description');
                $url = post('url');
                $payout = post('payout');
                $status = post('status');
                $offer_timestamp = date('Y-m-d H:i:s');
                $offer_type = post('offer_type');

                $is_public = post('selectPublic');


                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    $campaign_id = post('campaign');
                } else {
                    $campaign_id = Campaigns::getDefaultCampaignId();
                }

                $sql = "INSERT INTO offer(offer_name,description,url,payout,status,offer_timestamp, created_by, offer_type, is_public, campaign_id) VALUES(:offer_name,:description,:url,:payout,:status,:offer_timestamp, :created_by, :offer_type, :is_public, :campaign_id)";


                $stmt = $db->prepare($sql);

                $stmt->bindParam(":campaign_id", $campaign_id);

                $stmt->bindparam(":offer_name", $offer_name);
                $stmt->bindparam(":description", $description);
                $stmt->bindparam(":url", $url);
                $stmt->bindparam(":payout", $payout);
                $stmt->bindparam(":status", $status);
                $stmt->bindparam(":offer_type", $offer_type);
                $stmt->bindparam(":offer_timestamp", $offer_timestamp);
                $stmt->bindParam(":is_public", $is_public);
                $userID = Session::userID();
                $stmt->bindparam(":created_by", $userID);
                $stmt->execute();
                $lastOfferId = $db->lastInsertId();


                if (!empty($_POST["replist"]) || $is_public == 1) {
                    if (!empty($_POST["replist"])) {
                        $repIdArray = $_POST["replist"];
                    }

                    if ($is_public == 1) {

                        $affIds = User::selectAllAffiliateIDs()->fetchAll(PDO::FETCH_OBJ);

                        $repIdArray = array();

                        foreach ($affIds as $rep) {
                            $repIdArray[] = $rep->idrep;
                        }

                    }


                    $insertValues = array();
                    $repIdFinalQueryArray = array();
                    foreach ($repIdArray as $key => $repIdValue) {
                        $repIdFinalQueryArray[$key]["replist"] = $repIdValue;

                        $repIdFinalQueryArray[$key]["offer_idoffer"] = $lastOfferId;
                        //$affIdArray = array_merge($affIdArray, $affIdFinalQueryArray);
                        $repIdFinalQueryArray[$key]["payout"] = $payout;

                    }

                    $questionMarks = [];
                    foreach ($repIdFinalQueryArray as $key2) {

                        $questionMarks[] = "(?,?,?)";
                        $insertValues = array_merge($insertValues, array_values($key2));

                    }

                    $sql2 = 'INSERT INTO rep_has_offer (rep_idrep, offer_idoffer, payout) VALUES '.implode(',',
                            $questionMarks);
//                echo $sql2;
                    $stmt2 = $db->prepare($sql2);


                    $stmt2->execute($insertValues);
                }


                if (isset($_POST["enable_cap"])) {
                    $cap = new Caps($lastOfferId);

                    if ($_POST["cap_type"] == "click") {
                        $options["type"] = 0;
                    }

                    if ($_POST["cap_type"] == "conversion") {
                        $options["type"] = 1;
                    }


                    if ($_POST["cap_interval"] == "daily") {
                        $options["time_interval"] = 0;
                    }
                    if ($_POST["cap_interval"] == "weekly") {
                        $options["time_interval"] = 1;
                    }
                    if ($_POST["cap_interval"] == "monthly") {
                        $options["time_interval"] = 2;
                    }
                    if ($_POST["cap_interval"] == "total") {
                        $options["time_interval"] = Caps::total;
                    }

                    $options["interval_cap"] = $_POST["cap_num"];

                    $options["redirect_offer"] = $_POST["redirect_offer"];


                    $cap->createCapRules($options);

                }


                //$stmt2->debugDumpParams();

                $db->commit();

                if (isset($_POST["required_sales"])) {
                    $bonusOffer = new BonusOffer();
                    $bonusOffer->offer_id = $lastOfferId;
                    $bonusOffer->required_sales = $_POST["required_sales"];
                    $bonusOffer->save();
                }
            } catch (\Exception $e) {
                //An exception has occured, which means that one of our database queries
                //failed.
                //Print out the error message.
//                echo "ERROR = " . $e->getMessage();
                //Rollback the transaction.
                $db->rollBack();

                die("<h1> ERROR: OFFER NOT SAVED </h1>".$e->getMessage()); // If there is an error, DIE, escape function
            }

            send_to($redirect_to);  //If there is no errors redirect


        }

    }


} // end class

    