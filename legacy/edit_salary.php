<?php

$section = "salaries";
require('header.php');

$gets = new \LeadMax\TrackYourStats\Table\Assignments(["!id" => 0]);
$gets->getAssignments();
$gets->setGlobals();

$salaries = new \LeadMax\TrackYourStats\User\Salary($id);

$salary = $salaries->affiliateData;
if(!$salaries->hasSalary())
{
    $user = \LeadMax\TrackYourStats\User\User::SelectOne($id);
    $privi = new \LeadMax\TrackYourStats\User\Privileges();
    if($privi->findRepType($user->idrep) !== \App\Privilege::ROLE_AFFILIATE)
        send_to("home.php");

    $salary["user_name"] = $user->user_name;
    $salary["salary"] = 0;
    $salary["last_update"] = 0;

}
if(isset($_POST["id"]))
{
    if($salaries->hasSalary())
        \LeadMax\TrackYourStats\User\Salary::updateSalary($_POST["id"], $_POST["salary"], (int)$_POST["status"]);
    else
        \LeadMax\TrackYourStats\User\Salary::createSalary($_POST["id"], $_POST["salary"]);


    send_to("edit_salary.php?id={$_POST["id"]}");
}

?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span
                    class="lft">Edit <?= $salary["user_name"] ?>'s Salary</span>
        </div>


        <form action="edit_salary.php?id=<?=$id?>" method="post">


            <div class="white_box value_span8">
                <div class="left_con01">
                    <input type="hidden" name="id" value="<?=$id?>">

                    <p>
                        <label class="value_span9"><?= $salary["user_name"] ?></label>
                    </p>
                    <p>
                        <label>Salary </label>
                        <input type="number" value="<?= $salary["salary"] ?>" name="salary">
                    </p>

                    <p>
                        <label>Status</label>
                        <?php
                        ?>
                        <select name="status">
                            <?php
                            $active = $salary["status"] == 1 ? "selected" : "";
                            $inactive = $salary["status"] == 0 ? "selected" : "";
                            ?>
                            <option <?= $active ?> value="1">Active</option>
                            <option <?= $inactive ?> value="0">In-Active</option>
                        </select>
                    </p>


                </div>
                <div class="right_con01">
                    <p>
                        <label>Last Update</label>
                        <?php
                            if($salary["last_update"] == 0)
                                echo "<span>None</span>";
                            else
                            {
                                $date = \Carbon\Carbon::createFromFormat("U",$salary["last_update"])->toFormattedDateString();
                                echo " <span>{$date}</span>";
                            }
                        ?>
                    </p>

                    <input class="btn btn-primary" type="submit" value="Save" name="submit" >
                </div>
            </div>
        </form>


    </div>


    <?php
    include "footer.php";
    ?>


    <!--right_panel-->

