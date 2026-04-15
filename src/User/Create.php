<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/28/2017
 * Time: 12:23 PM
 */

namespace LeadMax\TrackYourStats\User;

use App\Privilege;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\System\Session;
use PDO;


// class to create users..
// stores functions and does all business logic for creating users

class Create
{


    public $assign;

    private $assignTos;

    private $listGod;

    private $listAdmin;

    private $listManager;


    private $type = array("is_admin" => "", "is_manager" => "", "is_rep" => "");


    function __construct()
    {


    }

    public static function activateAffiliate($id = null, $mid = null)
    {
        if ( (isset($_POST["button"]) && isset($_GET["id"])) || $id != null) {
            $affiliate_id = isset($_GET["id"]) ? $_GET["id"] : intval($id);
			$referrer_repid = isset($_POST["referrer_repid"]) ? $_POST["referrer_repid"] : $mid;

            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "UPDATE rep SET status = 1, referrer_repid = :referrer_repid WHERE idrep = :id";
            $prep = $db->prepare($sql);
            $prep->bindParam(":id", $affiliate_id);
            $prep->bindParam(":referrer_repid", $referrer_repid);
            $prep->execute();

            Tree::rebuild_tree(1, 1);

            Privileges::create($affiliate_id, \App\Privilege::ROLE_AFFILIATE);

            $permission = new Permissions();
            $permission->createPermissions(['aff_id' => $affiliate_id]);


            RepHasOffer::assignAffiliateToPublicOffers($affiliate_id);

            if (isset($_POST["referralSelectBox"])) {
                $options = [
                    'start_date' => $_POST["start_date"],
                    'end_date' => $_POST["end_date"],
                    'referral_type' => $_POST["referral_type"],
                    'payout' => $_POST["amount"],
                ];
                Referrals::addReferral($_POST["referralSelectBox"], $affiliate_id, $options);
            }

            Bonus::assignUsersInheritableBonuses([$affiliate_id], $referrer_repid);

            //User::sendWelcomeEmail($affiliate_id);

	        if ($id == null) {
		        send_to( "aff_update.php?idrep={$affiliate_id}" );
	        }

        }
    }


    public function printRadios()
    {
        echo "  <p class='value_span10'>";


        switch (Session::userType()) {
            case \App\Privilege::ROLE_GOD:
                echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">Agent
                    <input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . env('ACCOUNT_TYPE_TEXT') .
                    "<input {$this->type["is_admin"]} onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                break;

            case \App\Privilege::ROLE_ADMIN:
                echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">Agent
                    <input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . env( 'ACCOUNT_TYPE_TEXT' );
                if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_admins")) {
                    echo "<input {$this->type["is_admin"]} onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                }

                break;

            case \App\Privilege::ROLE_MANAGER:

                if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_affiliates")) {
                    echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">Agent ";
                }
                if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers")) {
                    echo "<input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . env( 'ACCOUNT_TYPE_TEXT' );
                }
                break;
        }


        echo "</p>";
    }


    // Wrapper
    public function dumpPermissionsToJavascript()
    {
        Session::permissions()->dumpPermissionsToJavascript();
    }


    public function dumpAssignablesToJavaScript()
    {
        $this->getAssignables();

        switch (Session::userType()) {
            case \App\Privilege::ROLE_GOD:
                $this->dumpGods();
                $this->dumpAdmins();
                $this->dumpManagers();
                break;

            case \App\Privilege::ROLE_ADMIN:
                if (Session::permissions()->can("create_admins")) {
                    $this->dumpGods();
                }
                $this->dumpAdmins();
                $this->dumpManagers();
                break;


            case \App\Privilege::ROLE_MANAGER:

                if (Session::permissions()->can("create_managers")) {
                    $this->dumpAdmins();
                }

                if (Session::permissions()->can("create_affiliates")) {
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

        if(Session::userType() == \App\Privilege::ROLE_ADMIN) {
            $id = Session::userID();
			$username = \App\User::where('idrep', $id)->first()->user_name;
			$this->listAdmin = [$id.';'.$username];
        }

		if (Session::userType() == \App\Privilege::ROLE_GOD) {
			$usernames = \App\Privilege::where('is_admin', 1)->join('rep', 'rep.idrep', '=', 'privileges.rep_idrep')->get();
			$usernameArray = array();
			foreach ($usernames as $username) {
				$usernameArray[] = $username->idrep.';'.$username->user_name.';';
			}
			$this->listAdmin = $usernameArray;

		}

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


    private function filterManagerAssignables()
    {
        $per = Session::permissions();
        $userData = Session::userData();

        foreach ($this->assignTos as $key => $val) {
            if ($val["is_admin"] == 1) {
                if ($per->can("create_managers")) {
                    if ($userData->referrer_repid != $val["idrep"]) {
                        unset($this->assignTos[$key]);
                    }
                } else {
                    unset($this->assignTos[$key]);
                }
            }

            // only show
            if ($val["is_manager"] == 1) {
                if ($val["idrep"] != $userData->idrep) {
                    unset($this->assignTos[$key]);
                }
            }

        }
    }


    public function getAssignables()
    {
        $new_replist = new User();
        $new_replist->user_id = Session::userID();

        if (Session::userType() == \App\Privilege::ROLE_ADMIN) {
            $this->assignTos = $new_replist->selectOwnedManagers()->fetchALL(PDO::FETCH_ASSOC);
        } else if (Session::userType() == \App\Privilege::ROLE_GOD) {
	        $this->assignTos = $new_replist->select_all_managers();
        } else {
            $this->assignTos = $new_replist->selectAssignablesManager();
        }

        if (Session::userType() == \App\Privilege::ROLE_MANAGER) {
            $this->filterManagerAssignables();
        }

		if(Session::permissions()->can("create_admins")) {
			$db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
			$sql = "SELECT * FROM rep INNER JOIN privileges ON privileges.rep_idrep = rep.idrep AND privileges.is_god = 1";
			$stmt = $db->prepare($sql);
			$stmt->execute();

			$gods = $stmt->fetchALL(PDO::FETCH_ASSOC);
			foreach ($gods as $key => $value) {
				$this->assignTos[] = $value;
			}
		}

        //dd($this->assignTos);

        $this->listGod = array();
        $this->listAdmin = array();
        $this->listManager = array();

        foreach ($this->assignTos as $key => $value) {
            $user_name = $value["user_name"];
            $idrep = $value["idrep"];

            if ($value["is_god"] == 1) {
                $this->listGod[] = $idrep.";".$user_name;
            }
            if ($value["is_admin"] == 1) {
                $this->listAdmin[] = $idrep.";".$user_name;

               /*  if ($idrep == Session::userID()) {
                    $this->listAdmin[] = $idrep.";".$user_name;
                } */
            }
            if ($value["is_manager"] == 1) {
                $this->listManager[] = $idrep.";".$user_name;
              /*   if ($value['referrer_repid'] == Session::userID()) {
                    $this->listManager[] = $idrep.";".$user_name;
                } */
            }
        }
    }
}