<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/27/2017
 * Time: 12:30 PM
 */

$section = "ip-blacklist";

require('header.php');


if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_GOD)
    send_to("home.php");

if(isset($_POST["start"]) && isset($_POST["end"]))
    if(\LeadMax\TrackYourStats\System\IPBlackList::createNewBlacklist($_POST["start"], $_POST["end"]))
        send_to("ip_black_list.php");
?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Add New IP Blacklist Range</span>

        </div>

        <div class=" white_box  value_span8 ">


            <form action="add_new_ip_blacklist.php" method="post">
                <div class="left_con01">


                    <p>
                        <label for="start">Start Range:</label>
                        <input type="text" name="start">
                    </p>
                    <p>
                        <label for="end">End Range:</label>
                        <input type="text" name="end">
                    </p>

                    <input type="submit" class="btn btn-primary " value="Create" >

                </div>
            </form>

        </div>
    </div>
</div>
<!--right_panel-->


<?php include 'footer.php'; ?>
