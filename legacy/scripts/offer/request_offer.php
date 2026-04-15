<?php




if(isset($_GET["id"]))
{
   $result = \LeadMax\TrackYourStats\Offer\RepHasOffer::requestOffer($_GET["id"], \LeadMax\TrackYourStats\System\Session::userID());
   die(json_encode($result));
}