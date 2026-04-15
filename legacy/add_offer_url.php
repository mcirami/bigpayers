<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/30/2017
 * Time: 11:16 AM
 */

$section = "offer-urls";

require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_urls")) {
    send_to("home.php");
}

if (isset($_POST['submit'])) {
    $URLs = new \LeadMax\TrackYourStats\Offer\URLs(\LeadMax\TrackYourStats\System\Company::loadFromSession());
    if ($URLs->createOfferURL($_POST["url"], $_POST["status"])) {
        send_to("offer_urls.php");
    }
}


?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Create Offer URL</span>

        </div>

        <div class="white_box_x_scroll white_box  value_span8 ">
            <div class="left_con01">

                <div class="" style="margin-bottom:20px">
                    <span class="alert alert-info">Point URL to this IP: <?= $_SERVER["SERVER_ADDR"] ?></span>
                </div>

                <form action="add_offer_url.php" method="post">
                    <p>
                        <label for="url">URL:</label>
                        <input type="text" name="url" value="">
                    </p>

                    <p>
                        <label for="status">Status:</label>
                        <select name="status">
                            <?php


                            echo "<option  value=\"1\"><span color='green'>Active</span></option>";
                            echo "<option value=\"0\"><span color='red'>In-Active</span></option>";

                            ?>

                        </select>
                    </p>


                    <input class="btn btn-default btn-success" type="submit" value="Create" name="submit">

                </form>
            </div>
        </div>
    </div>
    <!--right_panel-->


    <?php include 'footer.php'; ?>
