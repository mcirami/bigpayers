<?php

include "header.php";



if (isset($_GET["pcid"]) == false) {
    send_to('home.php');
}

$pendingConversionId = $_GET["pcid"];


//TODO add check for pending conversions!!??
//if (\LeadMax\TrackYourStats\Clicks\Conversion::doesLoggedInUserOwnConversion($conversion_id) == false) {
//    send_to("home.php");
//}


if (\LeadMax\TrackYourStats\Clicks\PendingConversion::selectOneQuery($pendingConversionId)->rowCount() < 0) {
    send_to("home.php");
}


$click = \LeadMax\TrackYourStats\Clicks\Click::SelectOne($conversion->click_id);

$offer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($click->offer_idoffer)->fetch(PDO::FETCH_OBJ);

?>


