<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/28/2017
 * Time: 12:23 PM
 */

namespace LeadMax\TrackYourStats\User;

use App\Privilege;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Session;
use PDO;


class Update
{

    public $affid = -1;

    public $assign;

    public $selectedUser;

    public $selectedUserType;

    public $selectedUserPriv;

    public $cannotDownGrade = false;

    public $cannotUpgrade = false;


    private $type = array("is_admin" => "", "is_manager" => "", "is_rep" => "");


    public $userType = -1;

    public $per;


    function __construct($assign)
    {

        if (!($assign instanceof \LeadMax\TrackYourStats\Table\Assignments)) {
            throw new \Exception("Must pass an Assignment object to constructor!");
        }


        $this->assign = $assign;

        if ($assign->has("idrep")) {
            $this->affid = $assign->get("idrep");
        }


        //if logged in user is an aff, they can only edit themself.
        $userType = Session::userType();
        if ($userType == Privilege::ROLE_AFFILIATE) {
            $this->affid = Session::userID();
        }

        $this->userType = $userType;

        $per = Permissions::loadFromSession();

        $this->per = $per;

        $this->selectedUserPriv = new Permissions($this->affid, true);


    }

    public function notifyIfCanChangePriviliges()
    {

        if (isset($_COOKIE["notify_aff_update"]) && $_COOKIE["notify_aff_update"] != 1) {


            if ($this->cannotUpgrade) {
                echo "<script type='text/javascript'>
              $.notify({

                title: 'Cannot',
                message: ' change this Affiliate\'s privileges, they have referrals. <br/>  <a onclick=\"document.cookie = \'notify_aff_update=1; expires=Thu, 18 Dec 2020 12:00:00 UTC;\';  $(\'.alert\').fadeOut(\'fast\');\" href=\"javascript:void(0);\">Don\'t notify me about this again.</a>'
            }, {
            placement: {
                from: 'top',
                align: 'center'
            },
                type: 'info',
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            }
        );
            

</script>";
            }

            if ($this->cannotDownGrade) {
                echo "<script type='text/javascript'>
              $.notify({

                title: 'Cannot',
                message: ' downgrade this User\'s privileges, they have users assigned to them. <br/>  <a onclick=\"document.cookie = \'notify_aff_update=1; expires=Thu, 18 Dec 2020 12:00:00 UTC;\';  $(\'.alert\').fadeOut(\'fast\');\" href=\"javascript:void(0);\">Don\'t notify me about this again.</a>'
            }, {
            placement: {
                from: 'top',
                align: 'center'
            },
                type: 'info',
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            }
        );
            

</script>";

            }


        }

    }

    public function dumpPermissionsToJavascript()
    {
        $this->selectedUserPriv->dumpPermissionsToJavascript(true);
    }

    public function selectUser()
    {

        //Select one record
        $user = new \LeadMax\TrackYourStats\User\User();
        $this->selectedUser = User::SelectOne($this->assign->get("idrep"));
        $this->selectedUserType = User::findRepType($this->assign->get("idrep"));

    }

    public function printReferrer()
    {
        if ($this->selectedUserType == \App\Privilege::ROLE_GOD) {
            return;
        }

        echo "  <p>
                    <label class=\"value_span9\">Manager</label>
  
                <select class=\"form-control input-sm \" id=\"referrer_repid\" name=\"referrer_repid\">";
        $new_replist = new \LeadMax\TrackYourStats\User\User();
        $result = $new_replist->select_all_assignables();

        foreach ($result as $key => $value) {
            $user_name = $value["user_name"];
            $idrep = $value["idrep"];

            echo $value["is_manager"];
            switch ($this->selectedUserType) {


                case \App\Privilege::ROLE_ADMIN:
                    if ($value["is_god"] == 1) {
                        if ($idrep == $this->selectedUser->referrer_repid) {
                            echo "<option selected value='$idrep'> $user_name </option>";
                        } else {
                            echo "<option value='$idrep'> $user_name </option>";
                        }


                    }
                    break;

                case Privilege::ROLE_MANAGER:

                    if ($value["is_admin"] == 1) {
                        if ($idrep == $this->selectedUser->referrer_repid) {
                            echo "<option selected value='$idrep'> $user_name </option>";
                        } else {
                            echo "<option value='$idrep'> $user_name </option>";
                        }
                    }
                    break;
                case Privilege::ROLE_AFFILIATE:
                    if ($value["is_manager"] == 1) {
                        if ($idrep == $this->selectedUser->referrer_repid) {
                            echo "<option selected value='$idrep'> $user_name </option>";
                        } else {
                            echo "<option value='$idrep'> $user_name </option>";
                        }
                    }
                    break;
            }


        }

        echo "   </select>
                    </p>";
    }


    public function clearLoginAttempts()
    {
        if (isset($_GET["clearAtt"]) && isset($_GET["idrep"]) && $_GET["clearAtt"] == 1) {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "UPDATE logins SET success = 3 WHERE rep_username = :user_name and date = :date";


            $prep = $db->prepare($sql);

            $date = date("Y-m-d");


            $prep->bindParam(":date", $date);
            $prep->bindParam(":user_name", $this->selectedUser->user_name);


            if ($prep->execute()) {
                send_to("aff_update.php?idrep={$this->assign->get("idrep")}&clearAtt=2");
            } else {
                \LeadMax\TrackYourStats\LeadMax\TrackYourStats\System\Log("clear User logins error", null);

            }
        }
    }

    public function getAffiliateOfferInfo()
    {

	    if ($this->selectedUserType != Privilege::ROLE_AFFILIATE) {
		    return false;
	    }

	    $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

	    $sql = "SELECT offer.offer_name, offer.idoffer, offer.payout FROM offer WHERE offer.status = 1 ORDER BY offer.idoffer";

	    $prep = $db->prepare($sql);

	    //$prep->bindParam(":repID", $this->selectedUser->idrep);
	    $prep->execute();

	    $result = $prep->fetchAll(PDO::FETCH_ASSOC);

	    foreach($result as $index => $row ) {
		    $resultHasOffer = DB::table('rep_has_offer')
		                        ->where('rep_has_offer.rep_idrep', '=', $this->selectedUser->idrep)
		                        ->where('rep_has_offer.offer_idoffer', '=', $row['idoffer'])
		                        ->select('rep_has_offer.payout')
		                        ->get();
		    $userCapStatus = DB::table('user_offer_caps')
		                       ->where('offer_idoffer', '=', $row['idoffer'])
		                       ->where('rep_idrep', '=', $this->selectedUser->idrep)->select('status', 'cap')->first();
		    if (count($resultHasOffer) > 0) {
			    $result[$index]["has_offer"] = true;
			    $result[$index]["repPayout"] = $resultHasOffer[0]->payout;
		    } else {
			    $result[$index]["has_offer"] = false;
			    $result[$index]["repPayout"] = 1.00;
		    }

		    $result[$index]["cap"] = $userCapStatus ? $userCapStatus->cap : 0;
		    $result[$index]["status"] = $userCapStatus ? $userCapStatus->status : 0;
	    }

	    foreach ($result as $row) {
		    echo "<tr>";
		    echo "<td>{$row['idoffer']}</td>";
		    echo "<td>{$row['offer_name']}</td>";
		    if ($this->userType != Privilege::ROLE_AFFILIATE) {
			    echo "<td>{$row['payout']}</td>";
		    }
		    if ($this->userType == Privilege::ROLE_AFFILIATE) {
			    echo "<td>{$row['repPayout']}</td>";
		    } else {
			    echo '<td>
						<input
						class="update_aff_payout"
						style="width:100px;" 
						type="number" 
						step="0.25" 
						id="offer_'.$row["idoffer"].'"
						data-offer="' . $row["idoffer"] . '" 
                        data-rep="' . $this->selectedUser->idrep . '" 
                        value="'.$row["repPayout"].'"/>
                     </td>';
		    }

		    if($this->userType != Privilege::ROLE_AFFILIATE) {
			    $this->getUserOfferAccess($row);
		    }

		    if($this->userType == Privilege::ROLE_GOD) {
				$this->getUserOfferCaps($row);
		    }

		    echo "</tr>";
	    }
    }

	private function getUserOfferAccess($row) {
		$hasAccess = $row['has_offer'] ? "checked" : "";
		echo '<td>
					<span class="offer_access">
						<input 
						class="offer_access_check" 
						type="checkbox" 
						data-rep="'. $this->selectedUser->idrep . '"
						data-offer="' . $row["idoffer"] .'"
						name="offer_access"' . $hasAccess . '>
						<label for="offer_access">Allow Access</label>
					</span>
				</td>';
	}

	private function getUserOfferCaps($row) {
		$capped = $row['status'] ? "checked" : "";
		 echo '<td>
					<span class="enable_offer_cap">
						<input 
						class="enable_offer_cap" 
						type="checkbox" 
						data-rep="'. $this->selectedUser->idrep . '"
						data-offer="' . $row["idoffer"] .'"
						name="enable_offer_cap"'
						. $capped . '>
						<label for="enable_offer_cap">Enable</label>
					</span>
				</td>
				<td>	
					<span>
						<input 
						class="user_offer_cap" 
						type="number" 
						step="1"
						data-rep="'. $this->selectedUser->idrep . '"
						data-offer="' . $row["idoffer"] .'"
						value="'. $row["cap"]  . '"
						name="user_offer_cap">
					</span>
				</td>';
	}

    public function updateAffiliatePayout()
    {

        Global $userType;


// update rep specific offer payout
        if (isset($_GET["offerid"]) && isset($_GET["out"]) && isset($_GET["idrep"]) && $userType != Privilege::ROLE_AFFILIATE) {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "UPDATE rep_has_offer SET payout=:payout WHERE rep_idrep = :repID AND offer_idoffer=:idoffer";
            $prep = $db->prepare($sql);

            $prep->bindParam(":payout", $_GET["out"]);
            $prep->bindParam(":repID", $_GET["idrep"]);
            $prep->bindParam(":idoffer", $_GET["offerid"]);

            if ($prep->execute()) {
                send_to("aff_update.php?idrep={$this->assign->get("idrep")}");
            } else {
                \LeadMax\TrackYourStats\System\Log("offer update error", null);

            }

        }
    }


    public function printRadios()
    {
        $this->findUserType();
        echo "<p class='value_span10'>";

        $cannotDownGrade = "";
        if (Tree::findChildren($this->selectedUser->lft, $this->selectedUser->rgt) !== 0) {
            $cannotDownGrade = "disabled";
            $this->cannotDownGrade = true;
        }

        $referrals = new Referrals($this->selectedUser->idrep);
        $cannotUpgrade = "";
        if ($referrals->hasReferrals()) {

            $cannotUpgrade = "disabled";
            $this->cannotUpgrade = true;
        }


        switch ($this->selectedUserType) {

            case \App\Privilege::ROLE_GOD:
                echo "<input type=\"hidden\" name=\"priv\" value=\"".Privilege::ROLE_GOD."\">";
                break;

            case \App\Privilege::ROLE_ADMIN:
                if ($this->per->can("create_affiliates")) {
                    echo "<input {$cannotDownGrade}  onclick=\"manager();appendPermissions();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\"  id=\"affRadio\" value=\"".Privilege::ROLE_AFFILIATE."\">Agent";
                }
                if ($this->per->can("create_managers")) {
                    echo "<input {$cannotDownGrade}  onclick=\"admin();appendPermissions();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_MANAGER."\">Manager";
                }
                if ($this->per->can("create_admins")) {
                    echo "<input  checked onclick=\"god();appendPermissions();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                }
                break;

            case Privilege::ROLE_MANAGER:
                if ($this->per->can("create_affiliates")) {
                    echo "<input {$cannotDownGrade}  onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\"  id=\"affRadio\" value=\"".Privilege::ROLE_AFFILIATE."\">Agent";
                }
                if ($this->per->can("create_managers")) {
                    echo "<input  checked onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_MANAGER."\">Manager";
                }
                if ($this->per->can("create_admins")) {
                    echo "<input   onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                }
                break;

            case Privilege::ROLE_AFFILIATE:
                if ($this->per->can("create_affiliates")) {
                    echo "<input  checked onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\"  id=\"affRadio\" value=\"".Privilege::ROLE_AFFILIATE."\">Agent";
                }
                if ($this->per->can("create_managers")) {
                    echo "<input  {$cannotUpgrade} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_MANAGER."\">Manager";
                }
                if ($this->per->can("create_admins")) {
                    echo "<input  {$cannotUpgrade}   onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                }
                break;


        }




        echo "</p>";

    }

    public function checkBox()
    {
        if ($this->type["is_admin"] == "checked") {
            echo "<script type='text/javascript'>appendAdmin();</script>";
        }

        if ($this->type["is_manager"] == "checked") {
            echo "<script type='text/javascript'>appendManager();</script>";
        }

        if ($this->type["is_rep"] == "checked") {
            echo "<script type='text/javascript'>appendAffiliate();</script>";
        }
    }


    public function dumpAssignablesToJavaScript()
    {
        $this->getAssignables();

        switch ($this->userType) {
            case \App\Privilege::ROLE_GOD:
                $this->dumpGods();
                $this->dumpAdmins();
                $this->dumpManagers();
                break;

            case \App\Privilege::ROLE_ADMIN:
                if ($this->per->can("create_admins")) {
                    $this->dumpGods();
                }
                $this->dumpAdmins();
                $this->dumpManagers();
                break;


            case Privilege::ROLE_MANAGER:
                if ($this->per->can("create_managers")) {
                    $this->dumpAdmins();
                } else {
                    true;
                }//if they cannot create managers, only assign affiliates to them self (instead of dumpManagers(), only assign to himself)


                if ($this->per->can("create_affiliates")) {
                    $this->dumpManagers();
                }


                break;

        }


    }

    public function dumpGods()
    {
        echo "<script type=\"text/javascript\">";
        echo "var listGod = ".json_encode($this->listGod).";";
        echo "</script>";

    }

    public function dumpAdmins()
    {
        echo "<script type=\"text/javascript\">";
        echo "var listAdmin = ".json_encode($this->listAdmin).";";
        echo "</script>";
    }

    public function dumpManagers()
    {
        echo "<script type=\"text/javascript\">";
        echo "var listManager = ".json_encode($this->listManager).";";
        echo "</script>";
    }


    public function findReferrerID()
    {

    }


    public function getAssignables()
    {
        $new_replist = new User();
        $this->assignTos = $new_replist->select_all_assignables();

        $this->listGod = array();
        $this->listAdmin = array();
        $this->listManager = array();


        foreach ($this->assignTos as $key => $value) {
            $first_name = $value["user_name"];
            $idrep = $value["idrep"];
            if ($value["is_god"] == 1) {
                $this->listGod[] = $idrep.";".$first_name;
            }
            if ($value["is_admin"] == 1) {
                $this->listAdmin[] = $idrep.";".$first_name;
            }
            if ($value["is_manager"] == 1) {
                $this->listManager[] = $idrep.";".$first_name;
            }


        }


    }


    public function findUserType()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $sql = "SELECT is_admin, is_manager, is_rep FROM privileges WHERE rep_idrep = :idrep";
        $prep = $db->prepare($sql);
        $prep->bindParam(":idrep", $this->affid);
        $prep->execute();
        $privResult = $prep->fetch(PDO::FETCH_ASSOC);


        if ($privResult["is_admin"] == 1) {
            $this->type["is_admin"] = "checked";
        }
        if ($privResult["is_manager"] == 1) {
            $this->type["is_manager"] = "checked";
        }
        if ($privResult["is_rep"] == 1) {
            $this->type["is_rep"] = "checked";
        }

    }


}