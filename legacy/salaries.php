<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/10/2017
 * Time: 4:12 PM
 */

$section = "salaries";
require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("pay_salaries"))
    send_to("home.php");


$gets = new \LeadMax\TrackYourStats\Table\Assignments(["id" => 0, "action" => 0, "payout" => 0, "reason" => ""]);
$gets->getAssignments();
$gets->setGlobals();


$salaries = new \LeadMax\TrackYourStats\User\Salary();

if ($id !== 0 && $action = "pay") {
    $salaries->payAffiliate($id, $payout, $reason);
}

$salaries->fetchAffiliateSalaries();

$userList = array();
foreach ($_POST as $name => $val) {

    $htmlInput = explode("_", $name);

    if (!isset($userList[(int)$htmlInput[0]]))
        $userList[(int)$htmlInput[0]] = array();

    if ($htmlInput[1] == "payout")
        $userList[(int)$htmlInput[0]]["payout"] = (double)$val;

    if ($htmlInput[1] == "reason")
        $userList[(int)$htmlInput[0]]["reason"] = $val;

    if ($htmlInput[1] == "id")
        $userList[(int)$htmlInput[0]]["salary_id"] = (int)$val;

}


if (!empty($userList))
    if ($salaries->payAllAffiliates($userList))
        send_to("salaries.php");

?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder value_span9"><span
                    class="lft">Affiliate Salaries</span>
        </div>


        <form action="salaries.php" method="post">

            <?php
            if (\LeadMax\TrackYourStats\System\Session::permissions()->can("pay_salaries")) {
                ?>

                <button style="margin-bottom: 20px;" class="btn btn-default btn-lg"><img
                            src="images/icons/money_dollar.png"> Pay All
                </button>

            <?php } ?>

            <?php
            if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_salaries")) {
                ?>
                <a style="margin-bottom: 20px;" href="edit_salaries.php" class="btn btn-default btn-lg"><img
                            src="images/icons/application_form_edit.png"> Edit Affiliate Salaries</a>
            <?php } ?>

            <div class="clear"></div>
            <div class="white_box manage_aff large_table value_span8">
                <table class="table table-bordered table_01 table-sm " id="mainTable">
                    <thead>
                    <tr>
                        <td>Affiliate Name</td>
                        <td>Salary</td>
                        <td>Status</td>

                        <td>Paid this Week</td>
                        <td>Reason</td>
                        <td>Last Update</td>
                        <td>Actions</td>
                    </tr>
                    </thead>
                    <tbody>


                    <?PHP

                    $affiliates = $salaries->weekReport()->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($affiliates as $aff) {

                        if ($aff["status"] == 1) {

                            echo "<tr>";
//                            echo "<td><a href='aff_update.php?repID='{$aff["idrep"]}>{$aff["user_name"]}</a></td>";
                            echo "<td>{$aff["user_name"]}</td>";
                            echo "<td><span>{$aff["salary"]}</span> ";

                            if ($aff["payout"] == null)
                                echo " <input type='hidden' name='{$aff["idrep"]}_id' value='{$aff["id"]}'>";

                            echo "</td>";


                            if ($aff["payout"] != null)
                                echo "<td><span style='color:green;'>PAID</span></td>";
                            else
                                echo "<td><span style='color:red;'>UN-PAID</span></td>";

                            if ($aff["payout"] == null && \LeadMax\TrackYourStats\System\Session::permissions()->can("edit_salaries"))
                                echo "<td><input type='number' name='{$aff["idrep"]}_payout' id='{$aff["idrep"]}_payout' value='{$aff["salary"]}'</td>";
                            else
                                echo "<td>{$aff["payout"]}</td>";

                            if ($aff["reason"] == null && $aff["reason"] !== "")
                                echo "<td><input type='text' name='{$aff["idrep"]}_reason'  id='{$aff["idrep"]}_reason'></td>";
                            else
                                echo "<td>{$aff["reason"]}</td>";


                            if ($aff["last_update"] !== null) {
                                $carboned = \Carbon\Carbon::createFromFormat("U", $aff["last_update"])->diffForHumans();
                                echo "<td>{$carboned}</td>";
                            } else
                                echo "<td></td>";


                            $alreadyPaidThisWeek = $aff["payout"] == null ? "" : "disabled";
                            echo "<td>";

                            if (\LeadMax\TrackYourStats\System\Session::permissions()->can("pay_salaries"))
                                echo "<a class='btn btn-default btn-sm' href='javascript:void(0);' onclick='payAffiliate({$aff["idrep"]});' {$alreadyPaidThisWeek}><img src='images/icons/money_add.png'> Pay</a>";

                            if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_salaries"))
                                echo "<a class='btn btn-default btn-sm' href='/user/{$aff["idrep"]}/salary/update' ><img src='images/icons/pencil.png'> Edit Salary</a>";


                            echo "</td>";
                            echo "</tr>";
                        }

                    }

                    ?>
                    </tbody>
                </table>


            </div>


    </div>
    </form>


</div>
<script type="text/javascript">


    function payAffiliate(user_id) {
        var url = "/salaries.php?id=" + user_id + "&payout=" + $("#" + user_id + "_payout").val() + "&reason=" + $("#" + user_id + "_reason").val();

        window.location = url;
    }

</script>

<script type="text/javascript">

    $(document).ready(function () {
        $("#mainTable").tablesorter(
            {
                widgets: ['staticRow']
            });
    });
</script>
<?php
include "footer.php";
?>


<!--right_panel-->

