<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 12/4/2017
 * Time: 3:38 PM
 */

include 'header.php';

if (isset($_GET["id"]) && isset($_GET["u"])) {
    if (\LeadMax\TrackYourStats\User\User::hasAffiliate($_GET["u"]) && \LeadMax\TrackYourStats\System\Session::permissions()->can("approve_offer_requests")) {
        if (\LeadMax\TrackYourStats\Offer\RepHasOffer::assignAffiliateToOffer($_GET["id"], $_GET["u"])) {


            $offer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($_GET["id"])->fetch(PDO::FETCH_OBJ);
            \LeadMax\TrackYourStats\System\Notifications::sendNotification($_GET["u"], 1, "Offer '{$offer->offer_name}' approved." ,"Offer {$offer->offer_name} was approved. <br/> This is an automated message. ");

            \LeadMax\TrackYourStats\System\Notify::info('Successfully ', 'assigned offer!', 3);
            send_to('notifications.php');
        } else {

            \LeadMax\TrackYourStats\System\Notify::error('Error assigning user to offer.', '', 3);
            send_to('notifications.php');
        }

    }
    else
    {

        \LeadMax\TrackYourStats\System\Notify::error('Error assigning user to offer.', '', 3);
        send_to('notifications.php');
    }
} else
    send_to('home.php');