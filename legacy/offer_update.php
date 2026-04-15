<?php

use Carbon\Carbon;
use LeadMax\TrackYourStats\Offer\Offer;

$section = "offers-list";
require('header.php');

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("create_offers")) {
    send_to("home.php");
}


$assign = new \LeadMax\TrackYourStats\Table\Assignments([
    "changeOfferStatus" => -1,
    "ast" => 0,
    "!idoffer" => -1,
]);

$assign->getAssignments();
$assign->setGlobals();
$assignType = $ast;

$update = new \LeadMax\TrackYourStats\Offer\Update($assign);

$offer_cap = new \LeadMax\TrackYourStats\Offer\Caps($idoffer);

if ($changeOfferStatus != -1 && \LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) {
    \LeadMax\TrackYourStats\Offer\Offer::ChangeOfferStatus($_GET["changeOfferStatus"], "/offer/manage");
    die();
}


$update->checkAndUpdate();
$rows = $update->selectedOffer;

if (isset($_GET["noAff"])) {
    $noAffiliates = unserialize(base64_decode($_GET["noAff"]));
}

?>

    <!--right_panel-->
    <div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span class="lft">Edit Offer <?php echo $idoffer; ?></span></div>
        <div class="white_box value_span8">

            <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="form"
                  enctype="multipart/form-data">
                <input type="hidden" name="idoffer" value="<?php echo $rows->idoffer; ?>">
                <div class="left_con01">
                    <?php

                    if (isset($noAffiliates)) {
                        echo "<p><span class='small_txt value_span10'>Managers that don't have affiliates cannot be assigned offers. <br/> ";

                        if (count($noAffiliates) == 1) {
                            echo $noAffiliates[0]->user_name." has no affiliates.";
                        } else {
                            echo "Managers ";

                            for ($i = 0; $i < count($noAffiliates); $i++) {
                                if ($i != count($noAffiliates) - 1) {
                                    echo $noAffiliates[$i]->user_name.", ";
                                } else {
                                    echo $noAffiliates[$i]->user_name." have no affiliates.";
                                }
                            }

                        }

                        echo "</span></p>";

                    }
                    ?>
                    <p>
                        <label class="value_span9">Name</label>
                        <input type="text" class="form-control" name="offer_name" maxlength="155"
                               value="<?php echo $rows->offer_name; ?>" id="offer_name"/>
                    </p>

                    <p>
                        <label class="value_span9">Visibility</label>
                        <select name="selectPublic" id="selectPublic">
                            <option value="1" <?= $rows->is_public == 1 ? "selected " : "" ?>>Public</option>
                            <option value="0" <?= $rows->is_public == 0 ? "selected" : "" ?>>Private</option>
                            <option value="2" <?= $rows->is_public == 2 ? "selected" : "" ?>>Requestable</option>
                        </select>
                    </p>


                    <?php if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) {

                        echo "<p>
                        <label class=\"value_span9\">Advertiser</label>
                        <select name=\"campaign\">";
                        $campaign = new \LeadMax\TrackYourStats\Offer\Campaigns(\LeadMax\TrackYourStats\System\Session::userType());
                        $campaigns = $campaign->selectCampaigns()->fetchAll(PDO::FETCH_OBJ);
                        foreach ($campaigns as $campaign) {
                            if ($campaign->id == $rows->campaign_id) {
                                echo "<option selected value=\"$campaign->id\">$campaign->name</option>";
                            } else {
                                echo "<option value=\"$campaign->id\">$campaign->name</option>";
                            }

                        }
                        echo "
                        </select>
                    </p>";
                    }
                    ?>

                    <p>
                        <label class="value_span9">Status</label>

                        <?php

                        if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) {
                            if ($rows->status == 1) {

                                echo "<select class=\"form-control input-sm \" onchange=\"areYouSure(this);\" id=\"status\" name=\"status\" value=\"1\"><option selected value=\"1\">Active</option>;<option value=\"0\">Disabled</option>;</select>";
                            } else {

                                echo "<select  class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option value=\"1\">Active</option>;<option selected value=\"0\">Disabled</option>;</select>";

                            }
                        } else {
                            if ($rows->status == 1) {

                                echo "<select disabled class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"0\"><option selected value=\"1\">Active</option>;<option value=\"0\">Disabled</option>;</select>";
                            } else {

                                echo "<select disabled class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"0\"><option value=\"1\">Active</option>;<option selected value=\"0\">Disabled</option>;</select>";

                            }
                        }
                        ?>


                    </p>
                    <p>
                        <label class="value_span9">Type</label>
                        <select class="form-control input-sm " id="offer_type" name="offer_type">

                            <?php
                            $isCPA = ($rows->offer_type == Offer::TYPE_CPA) ? "selected" : "";
                            $isCPC = ($rows->offer_type == Offer::TYPE_CPC) ? "selected" : "";
                            $isPendingConversion = ($rows->offer_type == Offer::TYPE_PENDING_CONVERSION) ? "selected" : "";

                            ?>
                            <option value="<?= Offer::TYPE_CPA ?>" <?= $isCPA ?> >CPA</option>

                            <option value="<?= Offer::TYPE_CPC ?>" <?= $isCPC ?> >CPC</option>


                            <option value="<?= Offer::TYPE_PENDING_CONVERSION ?>" <?= $isPendingConversion ?> >Pending
                                Conversion
                            </option>

                        </select>

                    </p>
                    <p>
                        <label class="value_span9">Description</label>
                        <input type="text" class="form-control" name="description" maxlength="555"
                               value="<?php echo $rows->description; ?>" id="description"/>
                    </p>
                    <p>
                        <label class="value_span9">URL</label>
                        <?php
                        if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) {
                            echo "<input type=\"text\" class=\"form-control\" name=\"url\" maxlength=\"555\"
                               value=\"$rows->url\" id=\"url\"/>";
                        } else {
                            echo "<input disabled type=\"text\" class=\"form-control\" name=\"url\" maxlength=\"555\"
                               value=\"$rows->url\" id=\"url\"/>";
                        }


                        ?>


                        <span class="small_txt value_span10">The offer URL where traffic will be directed to. The variables below can be used in offer URLs.</span>
                    <p class="value_span10">

                        When building offer url, these values will populate automatically:

                        <span class="small_txt value_span10">AffiliateID: #affid#</span>
                        <span class="small_txt value_span10">Username: #user#</span>
                        <span class="small_txt value_span10">Click ID: #clickid#</span>
                        <span class="small_txt value_span10">Offer ID: #offid#</span>
                    </p>
                    <p  class="value_span10">
                        When storing values Sub ID 1-5 on incoming clicks, these tags will populate the corresponding
                        values.

                        <span class="small_txt value_span10">Sub ID 1: #sub1#</span>
                        <span class="small_txt value_span10">Sub ID 2: #sub2#</span>
                        <span class="small_txt value_span10">Sub ID 3: #sub3#</span>
                        <span class="small_txt value_span10">Sub ID 4: #sub4#</span>
                        <span class="small_txt value_span10">Sub ID 5: #sub5#</span>


                    </p>
                    </p>

                    <span class="btn_yellow"> <input type="submit" name="button"
                                                     class="value_span6-2 value_span2 value_span1-2"
                                                     value="Update" onclick="return selectAll();"/></span>

                    <span class="btn_yellow" style="margin-left:2%;"> <a onclick="history.go(-1);"
                                                                         class="value_span6-2 value_span2 value_span1-2"
                        >Cancel</a></span>
                </div>
                <div class="right_con01">
                    <p>
                        <label class="value_span9">Payout</label>

                        <input type="text" class="form-control" name="payout" maxlength="12"
                               value="<?php echo $rows->payout; ?>" id="payout"/>
                        <span class="small_txt value_span10">The Amount paid to affiliates per conversion</span></p>

                    <p>


                        <script type="text/javascript">

                            <?php
                            echo "var cap_enabled = ".$offer_cap->offerHasCap().";";
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

                    <p class="value_span10">
                        <label class="value_span9">Offer Cap</label>

                        <input class="fixCheckBox" type="checkbox" id="enable_cap" name="enable_cap" value="enable_cap">Enable
                        Offer Cap
                    <span id="offer_cap_form" style="display:none;">

                        <span class="small_txt value_span10">Cap Type</span>
                        <select id="cap_type" name="cap_type" disabled>
                            <option <?php if ($offer_cap->getRuleVal("type") == \LeadMax\TrackYourStats\Offer\Caps::clicks) echo " selected " ?>
                                    value="click">Click
                            </option>
                            <option <?php if ($offer_cap->getRuleVal("type") == \LeadMax\TrackYourStats\Offer\Caps::conversions) echo " selected " ?>
                                    value="conversion">Conversion
                            </option>
                        </select>

                        <span class="small_txt value_span10">Cap Interval</span>
                        <select id="cap_interval" name="cap_interval" disabled>
                            <option<?php if ($offer_cap->getRuleVal("time_interval") == \LeadMax\TrackYourStats\Offer\Caps::daily) echo " selected " ?>
                                    value="daily">Daily
                            </option>
                            <option<?php if ($offer_cap->getRuleVal("time_interval") == \LeadMax\TrackYourStats\Offer\Caps::weekly) echo " selected " ?>
                                    value="weekly">Weekly
                            </option>
                            <option<?php if ($offer_cap->getRuleVal("time_interval") == \LeadMax\TrackYourStats\Offer\Caps::monthly) echo " selected " ?>
                                    value="monthly">Monthly
                            </option>


                            <option<?php if ($offer_cap->getRuleVal("time_interval") == \LeadMax\TrackYourStats\Offer\Caps::total) echo " selected " ?>
                                    value="total">Total
                            </option>

                        </select>

                        <span class="small_txt value_span10">Interval Cap</span>
                        <input type="number" name="cap_num" value="<?= $offer_cap->getRuleVal("interval_cap") ?>"
                               id="cap_num" disabled required/>

                        <span class="small_txt value_span10">Offer Redirect on Cap</span>

                        <?php
                        $offer_view = new \LeadMax\TrackYourStats\Offer\View(\LeadMax\TrackYourStats\System\Session::userType(),
                            $assign);
                        $offer_view->printToSelectBox("redirect_offer", $offer_cap->getRuleVal("redirect_offer"),
                            "disabled");

                        ?>

	                    <span class="cap_row">
		                    <label for="enable_hourly_cap" class="value_span9">Hourly Cap</label>
		                    <input class="fixCheckBox" type="checkbox" id="enable_hourly_cap" name="enable_hourly_cap"
		                           <?php if ($offer_cap->getRuleVal("hourly_cap_status")) : echo " checked"; endif; ?>
		                    >
		                    Enable Hourly Cap
		                    <span class="hourly_cap_wrap"
		                     <?php if(!$offer_cap->getRuleVal("hourly_cap_status")) : ?>
			                     style="display:none;"
		                     <?php endif; ?>
		                    >
			                    <span class="small_txt value_span10">Hourly Cap</span>
		                        <input type="number" name="hourly_cap_num" value="<?= $offer_cap->getRuleVal("hourly_cap") ?>"
		                               id="hourly_cap_num" <?php if(!$offer_cap->getRuleVal("hourly_cap_status")) : ?> disabled <?php endif; ?>/>
		                    </span>
	                    </span>
	                    <span class="max_cap cap_row">
		                    <label class="value_span9">Max Cap</label>
		                    <input class="fixCheckBox" type="checkbox" id="enable_max_cap" name="enable_max_cap"
		                           <?php if ($offer_cap->getRuleVal("max_cap_status")) : echo " checked"; endif; ?>
		                    >
		                    Enable Max Cap
	                       <span class="max_cap_wrap"
		                       <?php if(!$offer_cap->getRuleVal("max_cap_status")) : ?>
			                       style="display:none;"
		                       <?php endif; ?>
	                       >
		                        <span class="small_txt value_span10">Max Cap</span>
		                        <input type="number" name="max_cap_num" value="<?= $offer_cap->getRuleVal("max_cap") ?>"
		                               id="max_cap_num" <?php if(!$offer_cap->getRuleVal("max_cap_status")) : ?> disabled <?php endif; ?>/>
	                       </span>
	                    </span>



	                    <span class="range_block cap_row">
		                    <label for="enable_time_block" class="value_span9">Block Time Range</label>
		                    <input class="fixCheckBox" type="checkbox" id="enable_time_block" name="enable_time_block"
		                           <?php if ($offer_cap->getRuleVal("time_block_status")) : echo " checked"; endif; ?>
		                    >
		                    Enable Block Time Range
	                       <span class="time_block_wrap"
		                       <?php if(!$offer_cap->getRuleVal("time_block_status")) : ?>
			                       style="display:none;"
		                       <?php endif; ?>
	                       >
		                       <?php
		                            $start = $offer_cap->getRuleVal("block_start_time");
									$end = $offer_cap->getRuleVal("block_end_time");
									$startTime = "";
		                            $endTime = "";
									if ($start && $end) {
										$startTime = Carbon::createFromFormat( 'H:i:s', $start )->format( 'g:i A' );
										$endTime   = Carbon::createFromFormat( 'H:i:s', $end )->format( 'g:i A' );
									}
		                       ?>
		                       <span class="small_txt value_span10">Time Range To Block (EST)</span>
		                       <span class="time_select_row">
			                       <span>
				                       <label for='block_start_time'>From:</label>
										<input style='width:100px;'
										       id="block_start_time"
										       name="block_start_time"
										       value="<?= $startTime; ?>"
										/>
			                       </span>
			                       <span>
				                       <label for='block_end_time'>To:</label>
				                       <input style='width:100px;'
				                              id="block_end_time"
				                              name="block_end_time"
				                              value="<?= $endTime ?>"
				                       />
			                       </span>
		                       </span>
	                       </span>
	                    </span>

                    </span>

                    <?php
                    $update->findAssigned();
                    ?>

                    <p class="value_span10">
                        <?php
                        if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers")) {
                            $update->printRadios();
                        }

                        ?>


                    </p>
                    <!--                        <span class="small_txt value_span10">To select more than one user, hold CTRL and click. To select from a range, hold shift.</span>-->
                    <p>
                        <span class="small_txt value_span10">Assignned <?= $update->printType(); ?></span>
                        <select multiple onchange="moveToUnAssign(this)" class="form-control input-sm" id="replist"
                                name="replist[]">

                            <?php


                            //print assigned
                            $update->printAssigned();


                            ?>


                        </select>
                        <input type="text" id="assigned" onchange="searchSelectBox(this);" maxlength="25"
                               placeholder="Search for  <?= $update->printType(); ?>..."/>
                    </p>
                    <p>
                        <span class="small_txt value_span10"> <?= $update->printType(); ?></span>

                        <select multiple onchange="moveToAssign(this)" class="form-control input-sm" id="notAssigned"
                                name="notAssigned[]">

                            <?php


                            $update->printUnAssigned();


                            ?>


                        </select>
                        <input type="text" id="unAssigned" onchange=" searchSelectBox(this);" maxlength="25"
                               placeholder="Search for  <?= $update->printType(); ?>..."/>

                    </p>


                    <p style="margin-top:10px;">

                        <label class="value_span9">Offer Timestamp</label>

                        <input type="text" class="form-control" name="offer_timestamp" maxlength="19"
                               value="<?php echo $rows->offer_timestamp; ?>" id="offer_timestamp" disabled/>
                    </p>


                </div>

            </form>


        </div>
    </div>

    <script type="text/javascript" src="<?php echo $webroot; ?>js/offer.js"></script>
	<script>
		$(document).ready(function () {
			document.querySelector('#enable_max_cap').addEventListener('change', (e) => {
				if (e.target.checked) {
					document.querySelector('.max_cap_wrap').style.display = "block";
					document.querySelector('#max_cap_num').disabled = false;
				} else {
					document.querySelector('.max_cap_wrap').style.display = "none";
					document.querySelector('#max_cap_num').disabled = true;
				}
			})

			document.querySelector('#enable_hourly_cap').addEventListener('change', (e) => {
				if (e.target.checked) {
					document.querySelector('.hourly_cap_wrap').style.display = "block";
					document.querySelector('#hourly_cap_num').disabled = false;
				} else {
					document.querySelector('.hourly_cap_wrap').style.display = "none";
					document.querySelector('#hourly_cap_num').disabled = true;
				}
			})

			document.querySelector('#enable_time_block').addEventListener('change', (e) => {
				if (e.target.checked) {
					document.querySelector('.time_block_wrap').style.display = "block";
				} else {
					document.querySelector('.time_block_wrap').style.display = "none";
				}
			})
			$('#block_start_time').timepicker({
				timeFormat: 'h:mm a',
				interval: 30,
				minTime: '12:00am',
				maxTime: '11:59pm',
				dropdown: true,
				scrollbar: true
			});
			$('#block_end_time').timepicker({
				timeFormat: 'h:mm a',
				interval: 30,
				minTime: '12:00am',
				maxTime: '11:59pm',
				dropdown: true,
				scrollbar: true
			});
		});
	</script>

    <!--right_panel-->
<?php ?>

<?php include("footer.php"); ?>