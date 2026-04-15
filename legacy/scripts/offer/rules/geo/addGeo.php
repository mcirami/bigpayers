<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 10:47 AM
 */


//verify user session
$user = new LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session())
{
	send_to("login");
}

$geo = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo(json_decode($_POST["data"]));

if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($geo->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
{
	die("doesn't own offer");
}


$geo->createRule();





