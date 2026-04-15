<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/8/2017
 * Time: 3:34 PM
 */

require ("header.php");

$get = new \LeadMax\TrackYourStats\Table\Assignments(['!id' => -1]);
$get->getAssignments();
$get->setGlobals();


$offers = \LeadMax\TrackYourStats\Offer\Campaigns::selectCampaignOffers($id)->fetchAll(PDO::FETCH_OBJ);

$campaign = \LeadMax\TrackYourStats\Offer\Campaigns::selectCampaign($id)->fetch(PDO::FETCH_OBJ);

if(isset($_POST["id"]) && isset($_POST["name"]))
{
    if(\LeadMax\TrackYourStats\Offer\Campaigns::updateCampaign($_POST["id"], $_POST["name"]))
    {
        \LeadMax\TrackYourStats\System\Notify::info("Updated ", "advertiser successfully!", 3);
        send_to("campaign_edit.php?id={$campaign->id}");
    }
}

?>


<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder">
            <span class="lft value_span9">Edit Advertiser  <hspan>

        </div>


        <div class="clear"></div>
        <form action="campaign_edit.php?id=<?=$campaign->id?>" method="POST">

            <div class="white_box value_span8">

                <div class="left_con01">

                    <input type="hidden" value="<?=$campaign->id?>" name="id">


                    <p>
                        <label class="value_span9">Name</label>
                        <input type="text" class="form-control input-sm" name="name" maxlength="155" value="<?=$campaign->name?>" />
                    </p>


                    <p>


                    </p>


                </div>
                <div class="right_con01">
                    <p>
                        <label class="value_span9">Assigned Offers</label>
                        <select multiple >
                            <?php
                                foreach($offers as $offer)
                                    echo "<option>$offer->offer_name</option>";

                            ?>

                        </select>
                    </p>


                </div>


            </div>
            <span class="btn_yellow"> <input type="submit" name="button"
                                             class="value_span6-2 value_span2 value_span1-2"
                                             value="Update"/></span>
        </form>


    </div>

</div>
