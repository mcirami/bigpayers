<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/27/2017
 * Time: 1:02 PM
 */

$section = "ip-blacklist";

require('header.php');


if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_GOD)
    send_to("home.php");



$get = new \LeadMax\TrackYourStats\Table\Assignments(["!id" => -1]);
$get->getAssignments();
$get->setGlobals();

if(isset($_POST["start"]) && isset($_POST["end"]))
    if(\LeadMax\TrackYourStats\System\IPBlackList::updateBlackList($id, $_POST["start"], $_POST["end"]))
        send_to("ip_black_list.php");

$row = \LeadMax\TrackYourStats\System\IPBlackList::selectOne($id)->fetch(PDO::FETCH_OBJ);


?>




<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Edit IP Range</span>

        </div>

        <div class=" white_box  value_span8 ">


            <form action="edit_blacklisted_ip.php?id=<?=$row->id?>" method="post">
                <div class="left_con01">


                    <p>
                        <label for="start">Start Range:</label>
                        <input type="text" name="start" value="<?=long2ip($row->start)?>">
                    </p>
                    <p>
                        <label for="end">End Range:</label>
                        <input type="text" name="end" value="<?= long2ip($row->end)?>">
                    </p>

                    <input type="submit" class="btn btn-primary " value="Update" >

                </div>
            </form>

        </div>
    </div>
</div>
<!--right_panel-->


<?php include 'footer.php'; ?>
