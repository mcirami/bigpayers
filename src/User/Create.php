<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/28/2017
 * Time: 12:23 PM
 */

namespace LeadMax\TrackYourStats\User;

use App\Privilege;
use App\Support\CurrentUserSession;
use App\Support\LegacyDatabaseConnection as DatabaseConnection;
use App\Support\LegacyRepHasOffer as RepHasOffer;
use App\Support\NativeRequest;
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
        $requestId = NativeRequest::query("id");

        if ((NativeRequest::post("button") !== null && $requestId !== null) || $id != null) {
            $affiliate_id = $requestId !== null ? $requestId : intval($id);
			$referrer_repid = NativeRequest::post("referrer_repid", $mid);

            $db = DatabaseConnection::getInstance();
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

            $referralSelectBox = NativeRequest::post("referralSelectBox");

            if ($referralSelectBox !== null) {
                $options = [
                    'start_date' => NativeRequest::post("start_date"),
                    'end_date' => NativeRequest::post("end_date"),
                    'referral_type' => NativeRequest::post("referral_type"),
                    'payout' => NativeRequest::post("amount"),
                ];
                Referrals::addReferral($referralSelectBox, $affiliate_id, $options);
            }

            Bonus::assignUsersInheritableBonuses([$affiliate_id], $referrer_repid);

            //User::sendWelcomeEmail($affiliate_id);

	        if ($id == null) {
		        send_to("/user/{$affiliate_id}/edit");
	        }

        }
    }


    public function printRadios()
    {
        echo "  <p class='value_span10'>";


        switch (CurrentUserSession::type()) {
            case \App\Privilege::ROLE_GOD:
                echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">" . config('branding.affiliate.singular') . "
                    <input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . config('branding.account.singular') .
                    "<input {$this->type["is_admin"]} onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                break;

            case \App\Privilege::ROLE_ADMIN:
                echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">" . config('branding.affiliate.singular') . "
                    <input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . config('branding.account.singular');
                if (CurrentUserSession::permissions()->can("create_admins")) {
                    echo "<input {$this->type["is_admin"]} onclick=\"god();appendAdmin();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".Privilege::ROLE_ADMIN."\">Admin";
                }

                break;

            case \App\Privilege::ROLE_MANAGER:

                if (CurrentUserSession::permissions()->can("create_affiliates")) {
                    echo "<input {$this->type["is_rep"]} onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_AFFILIATE."\">" . config('branding.affiliate.singular') . " ";
                }
                if (CurrentUserSession::permissions()->can("create_managers")) {
                    echo "<input {$this->type["is_manager"]} onclick=\"admin();appendManager();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"".\App\Privilege::ROLE_MANAGER."\">" . config('branding.account.singular');
                }
                break;
        }


        echo "</p>";
    }


    // Wrapper
    public function dumpPermissionsToJavascript()
    {
        CurrentUserSession::permissions()->dumpPermissionsToJavascript();
    }


    public function dumpAssignablesToJavaScript()
    {
        $this->getAssignables();

        switch (CurrentUserSession::type()) {
            case \App\Privilege::ROLE_GOD:
                $this->dumpGods();
                $this->dumpAdmins();
                $this->dumpManagers();
                break;

            case \App\Privilege::ROLE_ADMIN:
                if (CurrentUserSession::permissions()->can("create_admins")) {
                    $this->dumpGods();
                }
                $this->dumpAdmins();
                $this->dumpManagers();
                break;


            case \App\Privilege::ROLE_MANAGER:

                if (CurrentUserSession::permissions()->can("create_managers")) {
                    $this->dumpAdmins();
                }

                if (CurrentUserSession::permissions()->can("create_affiliates")) {
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

        if(CurrentUserSession::type() == \App\Privilege::ROLE_ADMIN) {
            $id = CurrentUserSession::id();
			$username = \App\User::where('idrep', $id)->first()->user_name;
			$this->listAdmin = [$id.';'.$username];
        }

		if (CurrentUserSession::type() == \App\Privilege::ROLE_GOD) {
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
        $per = CurrentUserSession::permissions();
        $userData = CurrentUserSession::data();

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
        $new_replist->user_id = CurrentUserSession::id();

        if (CurrentUserSession::type() == \App\Privilege::ROLE_ADMIN) {
            $this->assignTos = $new_replist->selectOwnedManagers()->fetchALL(PDO::FETCH_ASSOC);
        } else if (CurrentUserSession::type() == \App\Privilege::ROLE_GOD) {
	        $this->assignTos = $new_replist->select_all_managers();
        } else {
            $this->assignTos = $new_replist->selectAssignablesManager();
        }

        if (CurrentUserSession::type() == \App\Privilege::ROLE_MANAGER) {
            $this->filterManagerAssignables();
        }

		if(CurrentUserSession::permissions()->can("create_admins")) {
			$db = DatabaseConnection::getInstance();
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

               /*  if ($idrep == CurrentUserSession::id()) {
                    $this->listAdmin[] = $idrep.";".$user_name;
                } */
            }
            if ($value["is_manager"] == 1) {
                $this->listManager[] = $idrep.";".$user_name;
              /*   if ($value['referrer_repid'] == CurrentUserSession::id()) {
                    $this->listManager[] = $idrep.";".$user_name;
                } */
            }
        }
    }
}
