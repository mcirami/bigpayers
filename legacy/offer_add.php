<?php

use LeadMax\TrackYourStats\Offer\Offer;

$section = "create-offer";
require('header.php');

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("create_offers")) {
    send_to("home.php");
}


$assign = new \LeadMax\TrackYourStats\Table\Assignments([
    "ast" => 0,
    'name' => '',
    'status' => 1,
    'desc' => '',
    'url' => '',
    'payout' => 0.00,

    "cap_enabled" => '0',
    "cap_type" => '',
    "cap_interval" => '',
    "cap_num" => 0,
    "redirect_offer" => '',
    'offer_type' => 0,
    'public' => 0,
    'parent' => 0,
], false, true);

$assign->getAssignments();
$assign->setGlobals();
$assignType = $ast;

$url = base64_decode($url);

$create = new \LeadMax\TrackYourStats\Offer\Create($assign);

$offer = new \LeadMax\TrackYourStats\Offer\RepHasOffer();
if ($assignType == 0) {
    $offer->CreateOfferWithRepHasOffer('/offer/manage');
} else {
    if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers")) {
        $offer->CreateOfferWithManagerAssign("/offer/manage");
    }
}


?>

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Create New Offer</span></div>
            <div class="white_box value_span8">

                <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="form"
                      enctype="multipart/form-data">

                  <div class="column_wrap">
                    <div class="left_con01 value_span7">
                        <p>
                            <label class="value_span9">Name</label>
                            <input id="offer_name" name="offer_name" type="text" value="<?= $assign->name; ?>"
                                   required/>
                        </p>


                        <p>
                            <label class="value_span9">Agent Access</label>
                            <select name="selectPublic" id="selectPublic">
                                <option value="1" <?= $assign->public == 1 ? "selected" : "" ?>>Public</option>
                                <option value="0"<?= $assign->public == 0 ? "selected" : "" ?>>Private</option>
                                <option value="2" <?= $assign->public == 2 ? "selected" : "" ?>>Requestable</option>
                            </select>
                        </p>

                        <?php if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) {

                            echo "<p>
                            <label class=\"value_span9\">Advertiser</label>
                            <select name=\"campaign\" required>";
                            $campaign = new \LeadMax\TrackYourStats\Offer\Campaigns(\LeadMax\TrackYourStats\System\Session::userType());
                            $campaigns = $campaign->selectCampaigns()->fetchAll(PDO::FETCH_OBJ);
                            foreach ($campaigns as $campaign) {
                                echo "<option value=\"$campaign->id\">$campaign->name</option>";
                            }
                            echo "</select>
                        </p>";

                        }

                        ?>

                        <p>
                            <label class="value_span9">Offer Type</label>
                            <select class="form-control input-sm " id="offer_type" name="offer_type">

                                <?php
                                $isCPA = ($assign->offer_type == Offer::TYPE_CPA) ? "selected" : "";
                                $isCPC = ($assign->offer_type == Offer::TYPE_CPC) ? "selected" : "";
                                $isPendingConversion = ($assign->offer_type == Offer::TYPE_PENDING_CONVERSION) ? "selected" : "";

                                ?>
                                <option value="<?= Offer::TYPE_CPA ?>" <?= $isCPA ?> >CPA</option>

                                <option value="<?= Offer::TYPE_CPC ?>" <?= $isCPC ?> >CPC</option>


                                <option value="<?= Offer::TYPE_PENDING_CONVERSION ?>" <?= $isPendingConversion ?> >
                                    Pending
                                    Conversion
                                </option>

                            </select>

                        </p>

                        <p>

                            <label class="value_span9">Status</label>
                            <select class="form-control input-sm " id="status" name="status">

                                <?php
                                $select1 = "";
                                $select2 = "";
                                if ($assign->status == 1) {
                                    $select1 = "selected";
                                } else {
                                    $select2 = "selected";
                                }

                                ?>
                                <option value="1" <?= $select1 ?> >Active</option>

                                <option value="0" <?= $select2 ?> >Disabled</option>

                            </select>
                        </p>
                        <p>
                            <label class="value_span9">Description</label>
                            <input type="text" class="form-control" name="description" maxlength="555"
                                   value="<?= $assign->get("desc"); ?>"
                                   id="description" required/>
                        </p>
                        <p>
                            <label class="value_span9">Offer Link</label>
                            <input type="text" class="form-control" name="url" maxlength="555" id="url"
                                   value="<?= $url; ?>"
                                   required/>
                            <span class="small_txt value_span10">The offer URL where traffic will be directed to. The variables below can be used in offer URLs.</span>
                        </p>
                        <p>

                            When building offer url, these values will populate automatically:

                            <span class="small_txt value_span10">AffiliateID: #affid#</span>
                            <span class="small_txt value_span10">Username: #user#</span>
                            <span class="small_txt value_span10">Click ID: #clickid#</span>
                            <span class="small_txt value_span10">Offer ID: #offid#</span>
                        </p>
                        <p>
                            When storing values Sub ID 1-5 on incoming clicks, these tags will populate the
                            corresponding values.

                            <span class="small_txt value_span10">Sub ID 1: #sub1#</span>
                            <span class="small_txt value_span10">Sub ID 2: #sub2#</span>
                            <span class="small_txt value_span10">Sub ID 3: #sub3#</span>
                            <span class="small_txt value_span10">Sub ID 4: #sub4#</span>
                            <span class="small_txt value_span10">Sub ID 5: #sub5#</span>


                        </p>
                    </div><!-- left_con01 -->

                    <div class="right_con01 value_span7">
                        <p>
                            <label class="value_span9">Agent Payout</label>

                            <input type="text" name="payout" maxlength="12" value="<?= $assign->get("payout"); ?>"
                                   id="payout" required/>
                            <span class="small_txt value_span10">The Amount paid per transaction.</span>

                        </p>


                        <script type="text/javascript">


                            <?php
                            echo "var cap_enabled = ".$assign->get("cap_enabled").";";
                            ?>

                            $(document).ready(function() {

                              $('#enable_bonus_offer').change(function() {
                                $('#enable_bonus_offer').attr('disabled', 'disabled');

                                if ($('#bonus_offer_div').css('display') === 'none') {
                                  $('#required_sales').removeAttr('disabled');
                                  $('#bonus_offer_div').slideDown('slow', function() {
                                    $('#enable_bonus_offer').removeAttr('disabled');
                                  });
                                }
                                else {
                                  $('#required_sales').attr('disabled', 'disabled');
                                  $('#bonus_offer_div').slideUp('slow', function() {
                                    $('#enable_bonus_offer').removeAttr('disabled');
                                  });

                                }
                              });

                              $('#enable_cap').change(function() {
                                $('#enable_cap').attr('disabled', 'disabled');
                                $('#enable_cap').attr('disabled', 'disabled');
                                var capForm = $('#offer_cap_form');

                                if (capForm.css('display') === 'none') {
                                  $('#cap_type').removeAttr('disabled');
                                  $('#cap_interval').removeAttr('disabled');
                                  $('#cap_num').removeAttr('disabled');
                                  $('#redirect_offer').removeAttr('disabled');
                                  capForm.slideDown('slow', function() {
                                    $('#enable_cap').removeAttr('disabled');

                                  });
                                }

                                else {
                                  $('#cap_type').prop('disabled', true);
                                  $('#cap_interval').prop('disabled', true);
                                  $('#cap_num').prop('disabled', true);
                                  $('#redirect_offer').prop('disabled', true);

                                  capForm.slideUp('slow', function() {
                                    $('#enable_cap').removeAttr('disabled');
                                  });

                                }

                              });

                              if (cap_enabled) {
                                $('#enable_cap').click();
                              }

                            });


                        </script>

                        <p>
                            <label class="value_span9">Offer Cap</label>

                            <input class="fixCheckBox" type="checkbox" id="enable_cap" name="enable_cap"> Offer Cap
                        <p id="offer_cap_form" style="display:none;">

                            <span class="small_txt value_span10">Cap Type</span>
                            <select id="cap_type" name="cap_type">
                                <option <?php if ($assign->get("cap_type") == "click") echo " selected " ?>
                                        value="click">Click
                                </option>
                                <option <?php if ($assign->get("cap_type") == "conversion") echo " selected " ?>
                                        value="conversion">Conversion
                                </option>
                            </select>

                            <span class="small_txt value_span10">Cap Interval</span>
                            <select id="cap_interval" name="cap_interval">
                                <option<?php if ($assign->get("cap_interval") == "daily") echo " selected " ?>
                                        value="daily">Daily
                                </option>
                                <option<?php if ($assign->get("cap_interval") == "weekly") echo " selected " ?>
                                        value="weekly">Weekly
                                </option>
                                <option<?php if ($assign->get("cap_interval") == "monthly") echo " selected " ?>
                                        value="monthly">Monthly
                                </option>
                                <option<?php if ($assign->get("cap_interval") == "total") echo " selected " ?>
                                        value="total">Total
                                </option>
                            </select>

                            <span class="small_txt value_span10">Interval Cap</span>
                            <input type="number" name="cap_num" value="<?= $assign->get("cap_num") ?>" id="cap_num"
                                   required/>

                            <span class="small_txt value_span10">Offer Redirect on Cap</span>

                            <?php
                            $offer_view = new \LeadMax\TrackYourStats\Offer\View($userType, $assign);
                            $offer_view->printToSelectBox("redirect_offer", $assign->redirect_offer);

                            ?>

                        </p>

                        </p>
                        <p>
                            <label class="value_span9">Bonus Offer</label>

                            <input class="fixCheckBox" type="checkbox" id="enable_bonus_offer"
                                   name="enable_bonus_offer"> Enable
                        <p id="bonus_offer_div" style="display:none;">
                            <label for="required_sales">Required Sales:</label>
                            <input type="number" name="required_sales" id="required_sales" value="0"
                                   style="width:100px" disabled>
                        </p>
                        </p>

                        <p>

                            <?php $create->printRadios(); ?>

                        </p>

                        <p>
                            <span class="small_txt value_span10">Assigned <?= $create->printType(); ?></span>
                            <select multiple onchange="moveToUnAssign(this)" class="form-control input-sm" id="replist"
                                    name="replist[]">
                            </select>
                            <input type="text" onchange="searchSelectBox(this);" maxlength="25" id="assigned"
                                   placeholder="Search for <?= $create->printType(); ?>..."/>


                            <span class="small_txt value_span10">Unassigned <?= $create->printType(); ?></span>
                            <select multiple onchange="moveToAssign(this)" class="form-control input-sm "
                                    id="notAssigned" name="notAssigned">
                                <?php
                                $create->printUnAssigned();
                                ?>
                            </select>
                            <input type="text" id="unAssigned" onchange="searchSelectBox(this);" maxlength="25"
                                   placeholder="Search for <?= $create->printType(); ?>..."/>

                            <span class="small_txt value_span10">To select more than one user, hold CTRL and click. To select from a range, hold shift.</span>
                        </p>
                        </p>
                    </div>
                  </div><!-- column_wrap -->
                  <span class="btn_yellow"> <input type="submit" name="button" class="value_span6-2 value_span2 value_span1-2"
                                                   value="Create" onclick="return selectAll();"/></span>
                </form>
            </div>


        </div>
    </div>

    <script type="text/javascript" src="<?php echo $webroot; ?>js/offer.js"></script>

    <script type="text/javascript">


      function changeAssignType(to) {

        var name = $('#offer_name').val();
        var status = $('#status').val();
        var desc = $('#description').val();
        var url = btoa($('#url').val());
        var payout = $('#payout').val();
        var publicOffer = $('#selectPublic').val();

        var capStringAmend = '';
        var offer_cap_enabled = $('#enable_cap').is(':checked');
        if (offer_cap_enabled) {
          var capType = $('#cap_type').val();
          var cap_interval = $('#cap_interval').val();
          var capNum = $('#cap_num').val();
          var redirect_offer = $('#redirect_offer').val();
          capStringAmend += '&cap_enabled=' + offer_cap_enabled + '&cap_type=' + capType + '&cap_interval=' +
              cap_interval + '&cap_num=' + capNum + '&redirect_offer=' + redirect_offer;
        }

        if (to === 'managers') {
          window.location = 'offer_add.php?ast=1&name=' + name + '&status=' + status + '&desc=' + desc + '&url=' + url +
              '&payout=' + payout + '&public=' + publicOffer + capStringAmend;
        } else {
          window.location = 'offer_add.php?ast=0&name=' + name + '&status=' + status + '&desc=' + desc + '&url=' + url +
              '&payout=' + payout + '&public=' + publicOffer + capStringAmend;
        }

      }


    </script>

    <!--right_panel-->


<?php include 'footer.php'; ?>