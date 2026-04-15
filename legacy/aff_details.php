<?php

$section = "affiliate-list";
require('header.php');

//Call Class
$na = new User\User();
//Select one record
if (isset($_GET['idrep'])) {
    $rows = \LeadMax\TrackYourStats\User\User::SelectOne($_GET['idrep']);
}



?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span class="lft"> <?php echo $rows->first_name . " " . $rows->last_name; ?>  </span></div>
        <div class="white_box value_span8">


            <div class="left_con01">
                <p>
                    <label class="value_span9">First Name</label>

                    <input disabled type="text" class="form-control" name="first_name" maxlength="155"
                           value="<?php echo $rows->first_name; ?>" id="first_name"/>
                </p>
                <p>
                    <label class="value_span9">Last Name</label>

                    <input disabled type="text" class="form-control" name="last_name" maxlength="155"
                           value="<?php echo $rows->last_name; ?>" id="last_name"/>
                </p>
                <p>
                    <label class="value_span9">Email</label>

                    <input disabled  type="text" class="form-control input-sm" name="email" maxlength="155"
                           value="<?php echo $rows->email; ?>" id="email"/>
                </p>
                <p>
                    <label class="value_span9">Cell Phone</label>

                    <input  disabled type="text" class="form-control input-sm" name="cell_phone" maxlength="155"
                           value="<?php echo $rows->cell_phone; ?>" id="cell_phone"/>
                </p>

<!--                <p>-->
<!--                    <label class="value_span9">Offers</label>-->
<!--                    --><?php //if ($rows->status == 1) {
//                        echo "<select disabled  class=\"form-control input-sm \" id=\"offers\" name=\"offers\" value=\"\">
//                                <option>Active</option>
//                                <option >Disabled</option>
//
//                             </select>";
//
//                    }
//                    ?>
<!---->
<!---->
<!--                </p>-->


            </div>
            <div class="right_con01">
                <p>
                    <label class="value_span9">Username</label>

                    <input disabled  type="text" class="form-control" name="user_name" maxlength="155"
                           value="<?php echo $rows->user_name; ?>" id="user_name"/>
                </p>
                <p>
                    <label class="value_span9">Password</label>

                    <input  disabled type="text" class="form-control" name="user_name" maxlength="155"
                           value="<?php echo $rows->password; ?>" id="user_name"/>
                </p>


                <p>
                    <label class="value_span9">Status</label>
                    <?php if ($rows->status == 1) {

                        echo "<select disabled  class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option selected value=\"1\">Active</option>;<option value=\"0\">Disabled</option>;</select>";
                    }else {

                        echo "<select disabled  class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option value=\"1\">Active</option>;<option selected value=\"0\">Disabled</option>;</select>";

                    }
                    ?>


                </p>
                <p>
                    <label class="value_span9">Referrer Rep ID</label>

                    <input disabled  type="text" class="form-control" name="referrer_repid" maxlength="10"
                           value="<?php echo $rows->referrer_repid; ?>" id="referrer_repid"/>
                </p>
                <p>
                    <label class="value_span9">Rep Timestamp</label>

                    <input  disabled type="text" class="form-control" name="referrer_repid" maxlength="10"
                           value="<?php echo $rows->rep_timestamp; ?>" id="referrer_repid"/>
                </p>





            </div>




    </div>


    <!--right_panel-->

    <?php include "footer.php"; ?>
