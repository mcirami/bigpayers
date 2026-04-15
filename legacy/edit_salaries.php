<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/10/2017
 * Time: 4:12 PM
 */

$section = "salaries";
require('header.php');

$salaries = new \LeadMax\TrackYourStats\User\Salary();

$salaries->fetchAffiliateSalaries();


?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span
                    class="lft">Edit Affiliate Salaries</span>
        </div>


        <form action="salaries.php" method="post">


            <div class="white_box value_span8">
                <table class="table table-bordered table_01 table-sm ">
                    <thead>
                    <tr>
                        <td>Affiliate Name</td>
                        <td>Salary</td>
                        <td>Status</td>
                        <td>Last Update</td>
                        <td>Actions</td>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    foreach ($salaries->affiliateList as $user) {
                        echo "<tr>";
                            echo "<td>{$user["user_name"]}</td>";
                            echo "<td>{$user["salary"]}</td>";

                            if ($user["status"] == 1)
                                echo "<td><span style='color:green;'>Active</span></td>";
                            else if ($user["status"] === 0)
                                echo "<td><span style='color:red;'>In-Active</span></td>";
                            else
                                echo "<td><span style='color:red;'>No Salary</span></td>";


                        if($user["last_update"] !== null)
                            {
                                $carboned = \Carbon\Carbon::createFromFormat("U", $user["last_update"])->diffForHumans();
                                echo "<td>{$carboned}</td>";
                                echo "<td><a class='btn btn-default btn-sm' href='/user/{$user["idrep"]}/salary/update'>Edit</a></td>";

                            }
                            else
                            {
                                echo "<td></td>";
                                echo "<td><a class='btn btn-default btn-sm' href='/user/{$user["idrep"]}/salary/create'>Create</a></td>";

                            }



                        echo "</tr>";
                    }
                    ?>


                    </tbody>
                </table>


            </div>


    </div>
    </form>


</div>


<?php
include "footer.php";
?>


<!--right_panel-->

