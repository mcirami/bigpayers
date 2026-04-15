<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/29/2017
 * Time: 10:47 AM
 */

include 'header.php';

if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_AFFILIATE)
    send_to('home.php');


if (isset($_POST["postback_url"])) {
    $result = \LeadMax\TrackYourStats\Offer\RepHasOffer::assignPostBackToAffiliatesOffers($_POST["postback_url"], \LeadMax\TrackYourStats\System\Session::userID(), $_POST["offerList"]);
    if ($result)
        \LeadMax\TrackYourStats\System\Notify::info('Successfully ', 'assigned post back url!');
    else
        \LeadMax\TrackYourStats\System\Notify::error('Error', ' assigning postback url. Please try again later or contact an administrator if the issue continues.');
}


?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder">
            <span class="lft value_span9">Mass Assign Post Back URL</span>

        </div>


        <div class="clear"></div>
        <form action="mass_assign_pb.php" method="POST">

            <div class="white_box value_span8">

                <div class="left_con01">


                    <p>
                        <label class="value_span9">Postback URL: (Conversion fires)</label>

                        <input type="text" class="form-control input-sm" name="postback_url" maxlength="155"
                               value="" id="postback_url"/>
                    </p>


                    <p>
                        <label class="value_span9">Variables</label>
                        <span>#affid#</span><br/>
                        <span>#user#</span><br/>
                        <span>#offid#</span> <br/>
                        <span>#clickid#</span> <br/>

                        <span class="small_txt value_span10">variables are auto inputed into your URL if they're found.</span>
                        <span class="small_txt value_span10">e.g. "google.com/?var1=<b>#affid#"</b> will translate to "google.com/?var1=<b>32</b>"</span>

                        <br/>
                        <br/>
                        <span class="small_txt value_span10">To store sub vars, have your software append to our offer url with sub1-sub5. Ex: &sub1=3213</span>
                        <span class="small_txt value_span10">Sub vars can be used in postback url as such:  #sub1#,     #sub2#    etc. </span>

                        <br/>
                        <br/>
                        <br/>
                        <span class="small_txt value_span10">When getting a url, if not otherwise specified, it is correct convention to seperate additionally vars with an ampersand </span>
                        <br/>
                        <span class="small_txt value_span10">Ex: http://google.com/?var1=#sub1#<b>&</b>var2=#sub2#</span>


                    </p>


                </div>
                <div class="right_con01" id="offers">

                    <a class="btn btn-default btn-sm" href="javascript:void(0);" onclick="checkBoxesInDiv('offers')">Check
                        All</a>
                    <a class="btn btn-default btn-sm" href="javascript:void(0);" onclick="unCheckBoxesInDiv('offers')">UnCheck
                        All</a>

                    <p>
                        <?php

                        $ownedOffers = \LeadMax\TrackYourStats\Offer\Offer::selectOwnedOffers(\LeadMax\TrackYourStats\System\Session::userType())->fetchAll(PDO::FETCH_OBJ);
                        foreach ($ownedOffers as $offer) {

                            echo "<label><input class='fixCheckBox' type='checkbox' name='offerList[]' value='{$offer->idoffer}'> {$offer->offer_name} </label>";
                        }

                        ?>
                    </p>

                </div>


            </div>
            <span class="btn_yellow"> <input type="submit" name="button"
                                             class="value_span6-2 value_span2 value_span1-2"
                                             value="Assign URL"/></span>
        </form>


    </div>

</div>

<?php include 'footer.php'; ?>

