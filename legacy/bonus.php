<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/2/2017
 * Time: 2:24 PM
 */


$section = "bonus";
require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("assign_bonuses") && !\LeadMax\TrackYourStats\System\Session::permissions()->can("create_bonuses"))
{
	send_to("home.php");
}


$bonuses = new \LeadMax\TrackYourStats\User\Bonus(\LeadMax\TrackYourStats\System\Session::userID(), true);


?>
	
	<!--right_panel-->
	<div class = "right_panel">
	<div class = "white_box_outer large_table">
	<div class = "heading_holder value_span9"><span class = "lft">Bonuses</span> <?php
		if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_bonuses"))
		{
			echo "<a style='margin-left: 1%; margin-top:.3%;' href=\"create_bonus.php\" class='btn btn-default btn-sm'><img src='/images/icons/add.png' >&nbsp;Create Bonus</a>";
		}
		
		if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD)
		{
			echo "<a style='margin-left: 1%; margin-top:.3%;' href=\"scripts/process_bonuses.php\" class='btn btn-default btn-sm'>Force Check Bonuses for Affiliates</a>";
		}
		
		?></div>
	<div class = "clear"></div>
	<div class = "  white_box value_span8 manage_aff large_table" style = "overflow-x: scroll;">
		
		<form action = "<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method = "post" id = "form"
			  enctype = "multipart/form-data">
			
			
			<div class = " left_con01">
				
				<table class = "table  table-bordered table_01" id = "mainTable">
					<thead>
					<tr>
						<td>Id</td>
						<td>Name</td>
						<td>Sales Required</td>
						<td>Payout</td>
						<td>Status</td>
						<?php
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_bonuses") || \LeadMax\TrackYourStats\System\Session::permissions()->can("assign_bonuses"))
						{
							echo "<td>Actions</td>";
						}
						
						?>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ($bonuses->bonuses as $bonus)
					{
						echo "<tr>";
						echo "<td>{$bonus["id"]}</td>";
						echo "<td>{$bonus["name"]}</td>";
						echo "<td>{$bonus["sales_required"]}</td>";
						echo "<td>{$bonus["payout"]}</td>";
						if ($bonus["is_active"] == 1)
						{
							echo "<td><span style='color:green'>Active</span></td>";
						}
						else
						{
							echo "<td><span style='color:red'>In-Active</span></td>";
						}
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_bonuses"))
						{
							echo "<td><a href='bonus_edit.php?id={$bonus["id"]}'>Edit Bonus</a></td>";
						}
						
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("assign_bonuses"))
						{
							echo "<td><a href='bonus_assign.php?id={$bonus["id"]}'>Assign Affiliates</a></td>";
						}
						
						echo "</tr>";
					}
					?>
					</tbody>
				</table>
			
			</div>
		
		
		</form>
	
	</div>
	
	
	<!--right_panel-->
	
	
	<script type = "text/javascript">
		
		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					widgets: ['staticRow']
				});
		});
	</script>


<?php include 'footer.php'; ?>