<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/10/2017
 * Time: 10:26 AM
 */


// Class to store create functions for the offer_add.php page,
//

namespace LeadMax\TrackYourStats\Offer;


use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;
use \LeadMax\TrackYourStats\User\User;

class Create
{

    public $assign;

    public $userType = -1;

    public $RepHasOffer;


    public $allAffiliates;


    function __construct($Assignments)
    {
        if (!($Assignments instanceof \LeadMax\TrackYourStats\Table\Assignments)) {
            throw new \Exception("Must pass an Assignments object to constructor!");
        }

        $this->assign = $Assignments;

//        Global $per; // instance of the permissions class set upon login.

        $per = Permissions::loadFromSession();


        // if they can create managers, they can assign managers to an offer, 'ast' is just a get setting to change back and forth between managers radio button and affiliates radio button on webpage
        if (!$per->can("create_managers")) {
            $this->assign->set("ast", 0);
        }

        $this->RepHasOffer = new RepHasOffer();

        // gets the defined user type on login i.e. admin, god, affiliate,
        Global $userType;
        $this->userType = $userType;

    }


    public function printUnAssigned()
    {
        $new_replist = new User();

        $per = Session::permissions();

        if ($this->assign->get("ast") == 0 && $per->can("create_managers")) {
            $result = $new_replist->select_all_reps();
        } else {
            if ($per->can("create_managers")) {
                $result = $new_replist->select_all_managers();
            } else {
                $result = $new_replist->selectAllManagerAffiliates(Session::userID())->fetchAll(\PDO::FETCH_ASSOC);
            }
        }


        foreach ($result as $key => $value) {
            echo "<option value='{$value["idrep"]}'>{$value["user_name"]}</option>";
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

        $per = Permissions::loadFromSession();

        if ($per->can("create_managers")) {
            echo "
                <input {$man} 
                    onchange=\"changeAssignType('managers');\"
                    type=\"radio\"
                    name=\"assignToType\" value=\"man\" style=\"width:2%;\"> Managers";
        }

        if ($per->can("create_managers")) {
            echo "
                <input {$aff}
                    onchange=\"changeAssignType('affiliates');\"
                    type=\"radio\"
                    name=\"assignToType\" value=\"aff\" style=\"width:2%;\">Affiliates
                    ";
        }

    }


}