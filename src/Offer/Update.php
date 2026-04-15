<?php

namespace LeadMax\TrackYourStats\Offer;

use App\BonusOffer;
use Carbon\Carbon;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Assignments;
use LeadMax\TrackYourStats\User\Tree;
use \LeadMax\TrackYourStats\User\User;
use PDO;

class Update
{


    public $selectedOffer = -1;

    public $offerID = -1;

    public $assign;

    public $assignedAffiliates = array();

    public $allAffiliates;

    public $RepHasOffer;

    public $userType = -1;

    function __construct($Assignments)
    {
        if (!($Assignments instanceof Assignments)) {
            throw new \Exception("Must pass an Assignments object to constructor!");
        }

        $this->assign = $Assignments;

        $this->offerID = $this->assign->get("idoffer");

        //Select one record

        $newOffer = new Offer();

        $this->offerID = $this->assign->get("idoffer");
        $this->selectedOffer = $newOffer->SelectOne($this->offerID);

        $this->RepHasOffer = new RepHasOffer();

        Global $userType;
        $this->userType = $userType;

    }


    public function printUnAssigned()
    {
        for ($i = 0; $i < count($this->allAffiliates); $i++) {

            //TODO Optimize check privileges
            /*TODO Query privileges table to return repids where is_god = 1, then parse into one dimential array like above, then in_array() compare with repid ($allReps[$nark][0]); */
            if ($this->allAffiliates[$i]['idrep'] != 1) {

                if (!in_array($this->allAffiliates[$i]["idrep"], $this->assignedAffiliates)) {
                    echo "<option   value='{$this->allAffiliates[$i]["idrep"]}' > {$this->allAffiliates[$i]["user_name"]} </option>";
                }


            }
        }
    }


    public function printAssigned()
    {

        $assignType = $this->assign->get("ast");
        if ($assignType == 1) {
            $managers = $this->RepHasOffer->selectAllAssignedManagers($this->offerID);
            foreach ($managers as $manager) {
                echo "<option value='{$manager->idrep}' > {$manager->user_name} </option>";
            }
        } else {
            for ($i = 0; $i < count($this->allAffiliates); $i++) {

                if (in_array($this->allAffiliates[$i]['idrep'], $this->assignedAffiliates)) {
                    echo "<option value='{$this->allAffiliates[$i]['idrep']}' > {$this->allAffiliates[$i]['user_name']} </option>";
                }


            }
        }
    }


    public function findAssigned()
    {

        $assignType = $this->assign->get("ast");
        $new_replist = new User();

        global $per;


        if ($this->userType == \App\Privilege::ROLE_MANAGER && !$per->can("create_managers")) {


            $userID = Session::userID();
            $this->allAffiliates = $new_replist->selectAllManagerAffiliates($userID)->fetchALL(PDO::FETCH_ASSOC);


            $assignedReps = $this->RepHasOffer->selectAllAssignedManagerAffiliates($this->offerID,
                $userID)->fetchAll(PDO::FETCH_ASSOC);


            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "SELECT rep_idrep FROM rep_has_offer INNER JOIN rep ON rep.idrep = rep_has_offer.rep_idrep AND rep.referrer_repid = :managerID WHERE offer_idoffer = :offerid";
            $prep = $db->prepare($sql);
            $prep->bindParam(":offerid", $this->offerID);
            $prep->bindParam(":managerID", $userID);
            $prep->execute();
            $repIDsWithOffer = $prep->fetchAll(PDO::FETCH_NUM);


            if ($assignType == 0) {
                // affiliates
                //parses multi dimential array into just normal array with repIDs
                for ($i = 0; $i < count($repIDsWithOffer); $i++) {
                    array_push($this->assignedAffiliates, $repIDsWithOffer[$i][0]);
                }
            } else {
                // managers
                //parses multi dimential array into just normal array with repIDs
                for ($i = 0; $i < count($assignedReps); $i++) {
                    if ($assignedReps) {
                        array_push($this->assignedAffiliates, $assignedReps[$i]['idrep']);
                    }
                }

            }


        }


        if ($this->userType == \App\Privilege::ROLE_GOD || $this->userType == \App\Privilege::ROLE_ADMIN || $per->can("create_managers")) {


            if ($assignType == 0) {
                $this->allAffiliates = $new_replist->select_all_num()->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $this->allAffiliates = $this->RepHasOffer->selectManagers($this->offerID);
            }


            if ($assignType == 0) {
                $assignedReps = $this->RepHasOffer->selectAllAssignedReps($this->offerID)->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $assignedReps = $this->RepHasOffer->selectAllAssignedManagers($this->offerID);
            }


            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "SELECT rep_idrep FROM rep_has_offer WHERE offer_idoffer = :offerid";
            $prep = $db->prepare($sql);
            $prep->bindParam(":offerid", $this->offerID);
            $prep->execute();
            $repIDsWithOffer = $prep->fetchAll(PDO::FETCH_NUM);


            if ($assignType == 0) {
                // affiliates
                //parses multi dimential array into just normal array with repIDs
                for ($i = 0; $i < count($repIDsWithOffer); $i++) {
                    array_push($this->assignedAffiliates, $repIDsWithOffer[$i][0]);
                }
            } else {
                // managers
                //parses multi dimential array into just normal array with repIDs
                for ($i = 0; $i < count($assignedReps); $i++) {
                    if ($assignedReps) {
                        array_push($this->assignedAffiliates, $assignedReps[$i]->idrep);
                    }
                }

            }


        }
    }


    public function printRadios()
    {
        $aff = "";
        $man = "";
        $assignType = $this->assign->get("ast");

        if ($assignType == 0) {
            $aff = "checked";
        } else {
            $man = "checked";
        }
        echo "
                <input {$man} 
                    onchange=\"window.location = 'offer_update.php?ast=1&idoffer={$this->offerID}';\"
                    type=\"radio\"
                    name=\"assignToType\" value=\"man\" style=\"width:2%;\"> Managers
                <input {$aff}
                    onchange=\"window.location = 'offer_update.php?ast=0&idoffer= {$this->offerID} ';\"
                    type=\"radio\"
                    name=\"assignToType\" value=\"aff\" style=\"width:2%;\">Affiliates
                    ";

    }


    public function checkAndUpdate()
    {
        $assignType = $this->assign->get("ast");

        if ($assignType == 0) {
            $this->UpdateOfferWithRepHasOffer("offer_update.php?idoffer={$this->assign->get("idoffer")}");
        } else {
            $this->UpdateOfferWithManager("offer_update.php?idoffer={$this->assign->get("idoffer")}");
        }

    }

    private function doesManagersHaveAffiliates($managerList)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT user_name, lft, rgt FROM rep WHERE idrep IN(";


        for ($i = 0; $i < count($managerList); $i++) {
            if ($i != count($managerList) - 1) {
                $sql .= "?,";
            } else {
                $sql .= "?)";
            }
        }

        $prep = $db->prepare($sql);
        $prep->execute($managerList);

        $managerList = $prep->fetchAll(PDO::FETCH_OBJ);

        $managerHasNoAffiliates = array();

        foreach ($managerList as $manager) {
            if (Tree::findChildren($manager->lft, $manager->rgt) == 0) {
                $managerHasNoAffiliates[] = $manager;
            }
        }


        return $managerHasNoAffiliates;
    }


    public function UpdateOfferWithManager($redirect_to)
    {
        $submit = post("button");
        if ($submit) {

            if (!empty($_POST["replist"])) {
                $managerList = $_POST["replist"];
                $managerIDList = array();

                $sql = "";

                for ($i = 0; $i < count($managerList); $i++) {
                    if ($i == 0) {
                        $sql .= "SELECT idrep, user_name FROM rep WHERE referrer_repid = ? ";
                    } else {
                        $sql .= " OR referrer_repid = ? ";
                    }

                    $managerIDList[] = $managerList[$i];
                }


                $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
                $prep = $db->prepare($sql);

                $prep->execute($managerIDList);

                $repIDlist = $prep->fetchAll(PDO::FETCH_NUM);


                $newID = array();

                for ($i = 0; $i < count($repIDlist); $i++) {
                    $newID[] = $repIDlist[$i][0];
                }

                $_POST["replist"] = $newID;
            }

            $_POST["notAssigned"] = array();


            $redirect_to .= "&ast=1";

            $noAffilaites = $this->doesManagersHaveAffiliates($managerIDList);

            if (!empty($noAffilaites)) {
                $redirect_to .= "&noAff=".base64_encode(serialize($noAffilaites));
            }

            $this->UpdateOfferWithRepHasOffer($redirect_to);


        }
    }


    public function deleteAffiliatesFromOffer($affIDs, $offerID)
    {


        $sql = "DELETE FROM rep_has_offer WHERE rep_idrep IN ( ";


        $i = 0;
        do {
            $sql .= " ? ";


            $i++;
            if ($i != count($affIDs)) {
                $sql .= " ,  ";
            } else {
                $sql .= " )";
            }
        } while ($i < count($affIDs));


        $affIDs[] = $offerID;


        $sql .= " AND offer_idoffer = ?";


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $prep = $db->prepare($sql);

        return $prep->execute($affIDs);
    }


    // UPDATE OFFER WITH REP
    public function UpdateOfferWithRepHasOffer($redirect_to)
    {
        $submit = post('button');

        if ($submit) {

            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $db->beginTransaction();

            try {

                $id = post('idoffer');

                $offer_name = post('offer_name');
                $description = post('description');

                $payout = post('payout');

                $offer_type = post('offer_type');

                $is_public = post('selectPublic');

                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    $campaign_id = post('campaign');
                }


                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    $url = post('url');
                    $status = post('status');
                }


                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    $sql = " UPDATE offer SET  offer_name =:offer_name,description =:description,url =:url,payout =:payout,status =:status, offer_type = :offer_type, is_public = :is_public, campaign_id = :campaign_id WHERE idoffer = :id ";
                } else {
                    $sql = " UPDATE offer SET  offer_name =:offer_name,description =:description,payout =:payout, is_public = :is_public WHERE idoffer = :id";
                }

                $stmt = $db->prepare($sql);


                if (Session::userType() == \App\Privilege::ROLE_GOD) {
                    $stmt->bindparam(":url", $url);
                    $stmt->bindparam(":status", $status);
                    $stmt->bindparam(":offer_type", $offer_type);
                    $stmt->bindParam(":campaign_id", $campaign_id);
                }


                $stmt->bindParam(":is_public", $is_public);
                $stmt->bindparam(":offer_name", $offer_name);
                $stmt->bindparam(":description", $description);
                $stmt->bindparam(":payout", $payout);

                $stmt->bindparam(":id", $id);


                $stmt->execute();


                // Before assigned, get list of all affiliates assigned to offer, compare with the ones being assign, and ignore ones that are already assigned.

                $lastOfferId = $id;
                $sql = "SELECT * FROM rep_has_offer WHERE offer_idoffer = :idoffer";

                $userID = Session::userID();

                if ($this->userType == \App\Privilege::ROLE_MANAGER) {
                    $allAssigned = $this->RepHasOffer->selectAllAssignedManagerAffiliates($id,
                        $userID)->fetchAll(PDO::FETCH_OBJ);
                } else {
                    $allAssigned = $this->RepHasOffer->selectAllAssignedReps($id)->fetchAll(PDO::FETCH_OBJ);
                }


                $repIdArray = array();


                if (isset($_POST["replist"])) {
                    foreach ($allAssigned as $key => $val) {
                        if (in_array($val->rep_idrep, $_POST["replist"])) {
                            if (($key2 = array_search($val->rep_idrep, $_POST["replist"])) !== false) {
                                unset($_POST["replist"][$key2]);
                            }
                        }

                    }
                }


                if (!empty($_POST["notAssigned"])) {
                    $this->deleteAffiliatesFromOffer($_POST["notAssigned"], $this->offerID);
                }

                if (isset($_POST["replist"])) {
                    if (!empty($_POST["replist"])) {

                        $repIdArray = $_POST["replist"];


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
//                var_dump($insertValues);

                        $sql2 = 'INSERT INTO rep_has_offer (rep_idrep, offer_idoffer, payout) VALUES '.implode(',',
                                $questionMarks);
//                echo $sql2;
                        $stmt2 = $db->prepare($sql2);

                        $stmt2->execute($insertValues);


                    }

                }


                $caps = new Caps($id, null, true);
                if (isset($_POST["enable_cap"])) {


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

	                if ($_POST["cap_interval"] == "hourly") {
		                $options["time_interval"] = 4;
	                }

                    if ($_POST["cap_interval"] == "total") {
                        $options["time_interval"] = Caps::total;
                    }

                    $options["interval_cap"] = $_POST["cap_num"];

                    $options["redirect_offer"] = $_POST["redirect_offer"];

	                if(isset($_POST["enable_max_cap"])) {
		                if(isset($_POST["max_cap_num"])) {
			                $options["max_cap"] = $_POST["max_cap_num"];
		                }
		                $options["max_cap_status"] = 1;
		                $tz = 'America/New_York';
		                $dateToday = \Illuminate\Support\Carbon::today($tz)->endOfDay()->format('Y-m-d H:i:s');
		                $carbonToday = Carbon::createFromFormat('Y-m-d H:i:s', $dateToday, $tz);
		                $options["max_cap_date"]  = $carbonToday->setTimezone("UTC");

	                } else {
		                $options["max_cap_status"] = 0;
		                $options["max_cap_date"] = null;
	                }

	                if(isset($_POST["enable_time_block"]) && isset($_POST["block_start_time"]) && isset($_POST["block_end_time"])) {
		                $postStart = str_replace(" ", ":00 ", $_POST["block_start_time"]);
		                $postEnd = str_replace(" ", ":00 ", $_POST["block_end_time"]);
		                $CarbonStart = Carbon::createFromFormat('H:i:s a', $postStart);
		                $CarbonEnd = Carbon::createFromFormat('H:i:s a', $postEnd);
		                $start = $CarbonStart->toTimeString();
		                $end = $CarbonEnd->toTimeString();

		                $options["block_start_time"]    = $start;
		                $options["block_end_time"]      = $end;
		                $options["time_block_status"]   = 1;

	                } else {
		                $options["time_block_status"]  = 0;
	                }

	                if(isset($_POST["enable_hourly_cap"])) {
		                $options["hourly_cap_status"]   = 1;
		                $options["hourly_cap"]          = $_POST["hourly_cap_num"];

	                } else {
		                $options["hourly_cap_status"]  = 0;
	                }

					$caps->updateOfferRules($options);
                } else {
					$caps->disableCap();
                }

//              $stmt2->debugDumpParams();

                $db->commit();

                $bonusOffer = BonusOffer::where('offer_id', '=', $lastOfferId)->first();

                if (isset($_POST["required_sales"])) {
                    if (is_null($bonusOffer)) {
                        $bonusOffer = new BonusOffer();
                        $bonusOffer->offer_id = $lastOfferId;
                    }
                    $bonusOffer->active = 1;
                    $bonusOffer->required_sales = $_POST["required_sales"];
                    $bonusOffer->save();
                } else {
                    if(!is_null($bonusOffer)){
                        $bonusOffer->active = 0;
                        $bonusOffer->save();
                    }
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

    function printType()
    {
        $assignType = $this->assign->get("ast");
        if ($assignType == 0) {
            return "Affiliates";
        }

        return "Managers";
    }


}