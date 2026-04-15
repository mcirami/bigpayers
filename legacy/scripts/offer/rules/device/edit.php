<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 12:07 PM
 */

//verify user session
$user = new \LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session() )
    send_to("login");


if(isset($_POST["ruleData"]))
{



    $ruleData = json_decode($_POST["ruleData"]); // rule data (rule ID, name, redirect_offer, etc)
    $deviceList = json_decode($_POST["data"]); // (country list)


    $edit = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Device($ruleData->ruleID);
    
    $edit->updateRule($ruleData, $deviceList);
    die("SUCCESS");
}


$edit = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Device($_GET["ruleID"]);


if(!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($edit->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
    die("doesn't own offer");



if(isset($_GET["getDevices"]))
    $edit->dumpDeviceList();

if(isset($_GET["ruleInfo"]))
    $edit->dumpRuleInfo();