<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/30/2017
 * Time: 1:04 PM
 */

$section = "add-referral";
require('header.php');

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_referrals"))
    send_to("home.php");


$assign = new \LeadMax\TrackYourStats\Table\Assignments(["!id" => -1]);

$assign->getAssignments();
$assign->setGlobals();


// checks if they have this affiliate assigned to them
if (!\LeadMax\TrackYourStats\User\User::hasAffiliate($id))
    send_to("home.php");


$report = new \LeadMax\TrackYourStats\User\Referrals($id);

if (isset($_POST["submit"])) {
    $options = [
        'start_date' => $_POST["start_date"],
        'end_date' => $_POST["end_date"],
        'referral_type' => $_POST["referral_type"],
        'payout' => $_POST["amount"]
    ];

    if (\LeadMax\TrackYourStats\User\Referrals::addReferral($id, $_POST["toRefer"], $options))
        send_to("aff_edit_ref.php?affid={$id}");
}


?>


<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span
                    class="lft">Add Referral for <?php echo $report->selectedAffiliate->user_name ?></span>
        </div>

        <div class="white_box value_span8 ">

            <!-- LEFT CON -->
            <div class="left_con01 ">

                <form action="add_referral.php?id=<?= $id ?>" method="POST">
                    <p id="referralP">


                        <label class="value_span9">Add Referral</label>
                    <div class="form-group">
                        <label style="font-size:12px;" for="amount">Affiliate to be Referred</label>

                        <select name="toRefer" class="form-control">
                            <?php

                            $affiliates = \LeadMax\TrackYourStats\User\Referrals::selectNoneAssignedReferralAffiliates($id)->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($affiliates as $affiliate)
                                echo "<option value=\"{$affiliate["idrep"]}\">{$affiliate["user_name"]}</option>";
                            ?>
                        </select>

                    </div>
                    <input id="referrer" name="referrer" type="hidden">
                    <input id="affid" name="affid" type="hidden">


                    <div class="form-group">
                        <label style="font-size:12px;" for="start_date">Start Date</label>
                        <input class="form-control" id="start_date" name="start_date" type="date" required>

                    </div>

                    <div class="form-group">

                        <label style="font-size:12px;" for="end_date">End Date (Empty for Indefinite)</label>
                        <input class="form-control" id="end_date" name="end_date" type="date">
                    </div>

                    <div class="form-group">

                        <label style="font-size:12px;" for="referral_type">Flat Fee / Percentage</label>
                        <select id="referral_type" name="referral_type" class="form-control" required>
                            <option value="flat" id="flat_fee">Flat Fee</option>
                            <option value="percentage" id="percentage">Percentage</option>
                        </select>
                    </div>

                    <div class="form-group">

                        <label style="font-size:12px;" for="amount">Amount / Percentage</label>
                        <input class="form-control" id="amount" name="amount" type="number" value="0" required step=".01">
                    </div>
                    <div class="form-group">

                        <label style="font-size:12px;" for="amount">Active</label>
                        <select id="is_active" name="is_active" class="form-control" required>
                            <option value="active" id="active">Active</option>
                            <option value="unactive" id="unactive">Un-active</option>
                        </select>
                    </div>

                    <div class="col-sm-4">

                        <button name="submit" type="submit" class="btn btn-success btn-default" id="saveBtn"><img
                                    src="/images/icons/table_save.png">Create Referral
                        </button>

                    </div>
                    <div class="col-sm-4">

                        <a  href="aff_edit_ref.php?affid=<?=$id?>" class="btn btn-default btn-default" id="goback"><img
                                    src="/images/icons/arrow_turn_left.png">Back
                        </a>
                    </div>

            </div>

            </form>


        </div>
        <!-- RIGHT CON -->
        <div class="right_con01">


        </div>


    </div>


</div>


</div>


<script type="text/javascript">


    $("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
    $("#end_date").datepicker({dateFormat: 'yy-mm-dd'});

    //load datepickers..
    $(function () {
        $("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
        $("#end_date").datepicker({dateFormat: 'yy-mm-dd'});

    });
</script>