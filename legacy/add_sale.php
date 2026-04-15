<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/26/2018
 * Time: 2:36 PM
 */

include "header.php";

if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::ADJUST_SALES) == false) {
    send_to('home.php');
}


$offers = \LeadMax\TrackYourStats\Offer\Offer::selectOwnedOffers(\LeadMax\TrackYourStats\System\Session::userType())->fetchAll(PDO::FETCH_OBJ);
$affiliates = \LeadMax\TrackYourStats\User\User::selectAllOwnedAffiliates(true)->fetchAll(PDO::FETCH_OBJ);


if (isset($_POST["affiliate"])) {
    $click = new \LeadMax\TrackYourStats\Clicks\Click();
    $click->rep_idrep = $_POST["affiliate"];
    $click->offer_idoffer = $_POST["offer"];
    $click->first_timestamp = $_POST["date"];
    $click->ip_address = $_SERVER["SERVER_ADDR"];
    $click->browser_agent = "TYS_GENERATED";
    $click->click_type = \LeadMax\TrackYourStats\Clicks\Click::TYPE_GENERATED;
    $click->save();

    $customPayout = isset($_POST["customPayout"]) ? $_POST["customPayout"] : false;
    $conversion = new \LeadMax\TrackYourStats\Clicks\Conversion();
    $conversion->timestamp = $_POST["date"];
    $conversion->click_id = $click->id;

    if ($customPayout) {
        $conversion->paid = $customPayout;
    }

    $conversion->registerSale();

    $log = new \LeadMax\TrackYourStats\Offer\AdjustmentsLog($conversion->id,
        \LeadMax\TrackYourStats\System\Session::userID());
    $log->setAction(\LeadMax\TrackYourStats\Offer\AdjustmentsLog::ACTION_CREATE_SALE);
    $log->log();

    \LeadMax\TrackYourStats\System\Notify::info("Successfully", " created sale!");

}


?>


<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span class="lft">Add Sale</span></div>
        <div class="white_box value_span8">

            <form action="add_sale.php" method="post" id="form" enctype="multipart/form-data">


                <div class="left_con01">
                    <p>
                        <label class="value_span9">Affiliate</label>
                        <select name="affiliate" id="affiliates">
                            <?php
                            foreach ($affiliates as $affiliate) {
                                if ($affiliate->status === 1) {
                                    echo "<option value=\"{$affiliate->idrep}\">{$affiliate->user_name}</option>";
                                }
                            }
                            ?>
                        </select>


                        <input type="text" id="affSearch" placeholder="Search affiliates..." style="margin-top:10px;"
                               onkeydown="if (event.keyCode == 13){return false;
}"/>

                    </p>

                    <p>
                        <label class="value_span9">Offer</label>
                        <select name="offer" id="offers">
                            <?php
                            foreach ($offers as $offer) {
                                if ($offer->status == 1) {
                                    echo "<option value=\"{$offer->idoffer}\">{$offer->offer_name}</option>";
                                }
                            }
                            ?>
                        </select>
                        <input type="text" id="offerSearch" placeholder="Search offers..." style="margin-top:10px;"
                               onkeydown="if (event.keyCode == 13){return false;
}"/>
                    </p>

                    <p>
                        <label class="value_span9">Date</label>
                        <input type="text" name="date" id="date" value="<?= date("Y-m-d H:i:s"); ?>">
                        <span class="small_txt value_span10">timestamps stored in utc</span>
                    </p>

                    <p>
                        <label class="value_span9"><input type="checkbox" class="fixCheckBox" id="customPayoutCheckBox">Custom
                            Payout</label>
                        <input disabled type="number" name="customPayout" id="customPayout" step="0.10" value="0.00">
                    </p>

                </div>

                <div class="right_con01">
                    <span class="small_txt value_span10">Note: Make sure the affiliate has the offer, or else it won't create sale.</span>
                </div>
        </div>
        <span class="btn_yellow"> <input type="submit" name="button" class="value_span6-2 value_span2 value_span1-2"
                                         value="Create Sale"/></span>
    </div>
</div>
<script type="text/javascript">

  function searchMultiSelect(multiSelectId, searchWord) {
    $('#' + multiSelectId + ' option').each(function(i, item) {
      if ($(item).text().toLowerCase().indexOf(searchWord.toLowerCase()) < 0) {
        $(item).hide();
      }
      else {
        $(item).show();
      }
    });

  }

  $(document).ready(function() {
    $('#date').datetimepicker({dateFormat: 'yy-mm-dd', timeFormat: 'hh:mm:ss'});

    $('#affSearch').change(function() {
      searchMultiSelect('affiliates', $('#affSearch').val());
    });

    $('#offerSearch').change(function() {
      searchMultiSelect('offers', $('#offerSearch').val());
    });

  });

  $('#customPayoutCheckBox').click(function(event) {
    if ($(this).prop('checked') === true) {
      $('#customPayout').prop('disabled', '');
    }
    else {
      $('#customPayout').prop('disabled', 'disabled');
    }
  });

</script>


<?php include 'footer.php'; ?>



