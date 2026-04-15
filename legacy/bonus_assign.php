<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/3/2017
 * Time: 11:51 AM
 */


$section = "bonus";
require('header.php');






if(!\LeadMax\TrackYourStats\System\Session::permissions()->can("assign_bonuses"))
    send_to("home.php");


$assign = new \LeadMax\TrackYourStats\Table\Assignments(["!id" => false]);
$assign->getAssignments();



$result = \LeadMax\TrackYourStats\User\Bonus::querySelectOne($assign->id)->fetch(PDO::FETCH_OBJ);

if(!$result)
    send_to("home.php");

if(isset($_POST["button"]))
{
    if(isset($_POST["removeList"]) && \LeadMax\TrackYourStats\User\Bonus::removeUsersFromBonus($assign->id, $_POST["removeList"])  )
        if(isset($_POST["replist"] )&& \LeadMax\TrackYourStats\User\Bonus::assignUsersToBonus($assign->id, $_POST["replist"]))
        {
            \LeadMax\TrackYourStats\System\Notify::info("Successfully", " added users to bonus!", 3);
            send_to("bonus_assign.php?id={$assign->id}");
        }

}




$assignedUsers = \LeadMax\TrackYourStats\User\Bonus::queryFindAssignedUsers($assign->id)->fetchAll(PDO::FETCH_ASSOC);

?>

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Assign Users to Bonus</span></div>
            <div class="white_box value_span8">

                <form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="form"
                      enctype="multipart/form-data">



                    <div class="left_con01">
                        <p>
                            <label class="value_span9">Name</label>
                            <input id="title" name="name" type="text" value="<?=$result->name?>" disabled/>
                        </p>
                        <p>

                            <label class="value_span9">Sales Required</label>
                            <input type="number" name="salesRequired" value="<?=$result->sales_required?>" disabled>
                        </p>

                        <p>
                            <label class="value_span9">Payout</label>
                            <input type="number" name="payout" value="<?=$result->payout?>" step="0.01" disabled>
                        </p>

                        <p>
                            <label class="value_span9">Status</label>
                            <select name="status" disabled>
                                <?php
                                $active = $result->is_active == 1 ? "selected" : "";
                                $inactive = $result->is_active == 0 ? "selected" : "";
                                ?>

                                <option <?=$active?> value="1">Active</option>
                                <option <?=$inactive?>  value="0">In-Active</option>
                            </select>
                        </p>


                        <?php
                        if(\LeadMax\TrackYourStats\System\Session::permissions()->can("create_admins"))
                        {

                            $admins = \LeadMax\TrackYourStats\User\User::selectAdmins()->fetchAll(PDO::FETCH_ASSOC);

                            $filteredAdmins = \LeadMax\TrackYourStats\User\Bonus::findAssignedUsers($admins, $assignedUsers);

                            ?>
                            <p>
                                <label>Admins</label>
                                <span class="small_txt value_span10">Assignned Admins</span>
                                <select multiple onchange="moveToSelect(this, 'assignedAdmins', 'unAssignedAdmins')" class="form-control input-sm" id="assignedAdmins"
                                        name="replist[]">
                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredAdmins["assignedUsers"]);
                                    ?>
                                </select>

                                <span class="small_txt value_span10">UnAssigned Admins</span>
                                <select multiple onchange="moveToSelect(this, 'unAssignedAdmins', 'assignedAdmins')" class="form-control input-sm "
                                        id="unAssignedAdmins" name="removeList[]">


                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredAdmins["userList"]);
                                    ?>
                                </select>
                            </p>

                        <?php } ?>

                    </div>

                    <div class="right_con01">

                        <?php
                        if(\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers")) {

                            $managers = \LeadMax\TrackYourStats\User\User::selectOwnedManagers()->fetchAll(PDO::FETCH_ASSOC);

                            $filteredManagers = \LeadMax\TrackYourStats\User\Bonus::findAssignedUsers($managers, $assignedUsers);


                            ?>
                            <p>
                                <label>Managers</label>
                                <span class="small_txt value_span10">Assignned Managers</span>
                                <select multiple onchange="moveToSelect(this, 'assignedManagers', 'unAssignedManagers')" class="form-control input-sm" id="assignedManagers"
                                        name="replist[]">
                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredManagers["assignedUsers"]);
                                    ?>
                                </select>

                                <span class="small_txt value_span10">UnAssigned Managers</span>
                                <select multiple onchange="moveToSelect(this, 'unAssignedManagers', 'assignedManagers')" class="form-control input-sm "
                                        id="unAssignedManagers" name="removeList[]">


                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredManagers["userList"]);
                                    ?>
                                </select>
                            </p>

                        <?php } ?>


                        <?php
                        if(\LeadMax\TrackYourStats\System\Session::permissions()->can("create_affiliates"))
                        {
                            $affiliates = \LeadMax\TrackYourStats\User\User::selectAllOwnedAffiliates()->fetchAll(PDO::FETCH_ASSOC);
                            $filteredAffiliates = \LeadMax\TrackYourStats\User\Bonus::findAssignedUsers($affiliates, $assignedUsers);

                            ?>
                            <p>
                                <label>Affiliates</label>
                                <span class="small_txt value_span10">Assignned Affiliates</span>
                                <select multiple onchange="moveToSelect(this, 'assignedAffiliates', 'unAssignedAffiliates')" class="form-control input-sm" id="assignedAffiliates"
                                        name="replist[]">
                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredAffiliates["assignedUsers"]);
                                    ?>
                                </select>

                                <span class="small_txt value_span10">UnAssigned Affiliates</span>
                                <select multiple onchange="moveToSelect(this, 'unAssignedAffiliates', 'assignedAffiliates')" class="form-control input-sm "
                                        id="unAssignedAffiliates" name="removeList[]">
                                    <?php
                                    \LeadMax\TrackYourStats\User\User::printUsersToSelectBox($filteredAffiliates["userList"]);
                                    ?>
                                </select>
                            </p>

                        <?php } ?>

                    </div>
            </div>

            <span class="btn_yellow"> <input id="submitBtn" type="submit" name="button" class="value_span6-2 value_span2 value_span1-2"
                                             value="Save" onclick="return selectAllBonuses();"/></span>

        </div>
        </form>

    </div>


    <!--right_panel-->


    <script type="text/javascript">




    </script>


<?php include 'footer.php'; ?>