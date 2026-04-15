<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 10:47 AM
 */


//verify user session
$user = new \LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session())
{
	send_to("login");
}


$handler = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Device(json_decode($_POST["data"]));

if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($handler->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
{
	die("doesn't own offer");
}


$handler->createRule();





