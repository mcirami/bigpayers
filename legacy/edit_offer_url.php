<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/25/2017
 * Time: 3:28 PM
 */

$section = "offer-urls";

require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_urls"))
    send_to("home.php");

$get = new \LeadMax\TrackYourStats\Table\Assignments(["!id" => -1]);
$get->getAssignments();
$get->setGlobals();

if(isset($_POST['submit']))
{
    $URLs = new \LeadMax\TrackYourStats\Offer\URLs(\LeadMax\TrackYourStats\System\Company::loadFromSession());
    if($URLs->updateOfferUrl((int)$id, $_POST['status'], $_POST['url']))
        send_to('offer_urls.php');
}

$URLs = new \LeadMax\TrackYourStats\Offer\URLs(\LeadMax\TrackYourStats\System\Company::loadFromSession());

$url = $URLs->selectOne($id,\LeadMax\TrackYourStats\System\Company::loadFromSession()->getID())->fetch(PDO::FETCH_OBJ);


?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Edit Offer URL</span>

        </div>

        <div class="white_box_x_scroll white_box  value_span8 ">
            <div class="left_con01">


                <form action="edit_offer_url.php?id=<?=$get->id?>" method="post">
                    <p>
                        <label for="url">URL:</label>
                        <input type="text" name="url" value="<?= $url->url ?>">
                    </p>

                    <p>
                        <label for="status">Status:</label>
                        <select name="status">
                            <?php

                            $active = $url->status == 1 ? "selected" : "";
                            $inActive = $url->status == 1 ? "" : "selected";

                            echo "<option {$active} value=\"1\"><span color='green'>Active</span></option>";
                            echo "<option {$inActive} value=\"0\"><span color='red'>In-Active</span></option>";

                            ?>

                        </select>
                    </p>

                    <p>
                        <label for="timestamp">Timestamp:</label>
                        <?php echo \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $url->timestamp)->toFormattedDateString() ?>
                    </p>

                    <input class="btn btn-default btn-success" type="submit" value="Save" name="submit">

                </form>
            </div>
        </div>
    </div>
    <!--right_panel-->


    <?php include 'footer.php'; ?>
