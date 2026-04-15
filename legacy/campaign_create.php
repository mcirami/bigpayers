<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/6/2017
 * Time: 4:00 PM
 */


require('header.php');

$campaigns = new \LeadMax\TrackYourStats\Offer\Campaigns(\LeadMax\TrackYourStats\System\Session::userType());


if(isset($_POST["offer_name"]))
{
    $campaignID = \LeadMax\TrackYourStats\Offer\Campaigns::createCampaign($_POST["offer_name"]);

    if($campaignID)
    {
            \LeadMax\TrackYourStats\System\Notify::info("Successfully created advertiser", "!");
    }

}



?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span class="lft">Create Advertiser </span></div>
        <div class="white_box value_span8">

            <form action="campaign_create.php" method="post" id="form"
                  enctype="multipart/form-data">


                <div class="left_con01 value_span7" style="width: 100%;">
                    <p>
                        <label class="value_span9">Name</label>
                        <input id="offer_name" name="offer_name" type="text" value=""
                               required/>
                    </p>


                </div>

                <div class="right_con01">
<!--                    <p>-->
<!--                        <label class="value_span0">Assign Offers</label>-->
<!--                        <select multiple name="offers[]" id="assigned" onchange="moveToUnAssign(this)">-->
<!---->
<!--                        </select>-->
<!--                    </p>-->
<!--                    <p>-->
<!--                        <label class="value_span9">Un-Assigned Offers</label>-->
<!--                        <select multiple name="unAssignedOffers" id="unAssigned" onchange="moveToAssign(this)">-->
<!---->
<!---->
<!--                            --><?php
//                            $offers = $campaigns->queryGetOffers()->fetchAll(PDO::FETCH_OBJ);
//                            foreach ($offers as $offer) {
//                                echo "<option value=\"$offer->idoffer\">$offer->offer_name</option>";
//                            }
//                            ?>
<!---->
<!--                        </select>-->
<!--                    </p>-->
                </div>
              <span class="btn_yellow"> <input type="submit" name="button" class="value_span6-2 value_span2 value_span1-2"
                                               value="Create" onclick="return selectAllMultiSelect('assigned');"/></span>
        </div>


    </div>
</div>
<script type="text/javascript">

    function moveToUnAssign(ele)
    {
        var affName = "";
        var html = "";

        $('#assigned :selected').each(function(i, sel){

            html += "<option value=\"" + sel.value + "\"> " + sel.text + "</option>";

            sel.remove();
        });
        $("#unAssigned").append(html);
    }

function moveToAssign(ele)
{
    var affName = "";
    var html = "";

    $('#unAssigned :selected').each(function(i, sel){

        html += "<option value=\"" + sel.value + "\"> " + sel.text + "</option>";

        sel.remove();
    });
    $("#assigned").append(html);
}



</script>


<?php include 'footer.php'; ?>
