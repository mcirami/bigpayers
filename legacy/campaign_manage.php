<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/6/2017
 * Time: 3:49 PM
 */


require('header.php');


$campaigns = new \LeadMax\TrackYourStats\Offer\Campaigns(\LeadMax\TrackYourStats\System\Session::userType());

$selectedCampaigns = $campaigns->selectCampaigns()->fetchAll(PDO::FETCH_OBJ);

?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer">
        <div class="heading_holder value_span9"><span class="lft"> Advertisers</span></div>

        <div class="white_box manage_aff white_box_x_scroll large_table value_span8">


            <table class="table table-condensed table-bordered table_01" id="mainTable">
                <thead>
                <tr>
                    <td>ID</td>
                    <td>Name</td>
                    <td>Actions</td>
                </tr>
                </thead>
                <tbody>
                <?php
                    foreach($selectedCampaigns as $campaign)
                    {
                        echo "<tr>";
                            echo "<td>$campaign->id</td>";
                            echo "<td>$campaign->name</td>";
                            echo "<td><a class='btn btn-default btn-sm' href='campaign_edit.php?id=$campaign->id'>Edit</a></td>";
                        echo "</tr>";
                    }

                ?>
                </tbody>
            </table>
        </div>
    </div>
	
	<script type = "text/javascript">
		
		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					widgets: ['staticRow']
				});
		});
	</script>

    <?php include 'footer.php'; ?>
