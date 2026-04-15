<?php
include 'header.php';



?>


    <!--right_panel-->
    <div class="right_panel_account">


        <div class="white_box_outer">
            <div class="heading_holder">
                <span class="lft value_span9">My Account</span>
            </div>
            <div class="clear"></div>
            <div class="white_box value_span8">

                <div class="com_acc">
                    <p><span class="lft value_span9">Name</span><span
                                class="rt value_span10"><?php echo \LeadMax\TrackYourStats\System\Session::userData()->first_name; ?></span>
                    </p>
                    <p><span class="lft value_span9">E-mail:</span><span class="rt "><a
                                    href="mailto:<?php \LeadMax\TrackYourStats\System\Session::userData()->email ?>"><?php echo \LeadMax\TrackYourStats\System\Session::userData()->email ?></a></span>
                    </p>
                    <p><span class="lft value_span9">Password</span><span class="rt value_span10"><a
                                    href="<?= $webroot."aff_update.php?idrep=".\LeadMax\TrackYourStats\System\Session::userID() ?>">Update Password</a></span>
                    </p>

                    <?php
                    if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_postback")) {
                        echo "<p><span class=\"lft value_span9\">PostBack URL:</span>";
                        $url1 = $webroot."?uid=".\LeadMax\TrackYourStats\System\Company::loadFromSession()->getUID()."&clickid=";
//                        echo "<p> <span id=\"pb1\" class=\"rt blue_txt\">{$url1}</span> <button onclick=\"copyToClipboard(getElementById('pb1'));\"  class='btn btn-default btn-sm'><img src='/images/icons/page_copy.png' alt='Copy'></button></p>";


//                        echo "<p> <span id=\"pb2\" class=\"rt blue_txt\">{$url2}</span> <button onclick=\"copyToClipboard(getElementById('pb2'));\"  class='btn btn-default btn-sm'><img src='/images/icons/page_copy.png' alt='Copy'></button></p>";

                    }

                    ?>


                    <div class="btn btn-default">
                        <a class="value_span11 value_span2 value_span4"
                           href="aff_update.php?idrep=<?php echo \LeadMax\TrackYourStats\System\Session::userID(); ?>">Update Account</a>
                    </div>
                </div><!-- com_acc -->
            </div><!-- white_box -->
        </div><!-- white_box_outer -->
    </div>
    <!--right_panel-->

<?php include 'footer.php'; ?>