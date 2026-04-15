<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/23/2018
 * Time: 3:06 PM
 */

include "header.php";

if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\User\Permissions::EDIT_REPORT_PERMISSIONS) == false)
{
	send_to('home.php');
}

$affiliateList = \User\ReportPermissions::getAffiliates();

if (isset($_POST["button"]))
{
	$reportPermission = new \User\ReportPermissions();
	$reportPermission->saveFromPOST();
	send_to('aff_permissions.php');
}

?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table ">
		<div class = "heading_holder">
			
			<span class = "lft value_span9">Affiliate Report Permissions</span>
		
		</div>
		<div class = "form-group searchDiv">
			
			<input id = "searchBox" onkeyup = "searchTable()" class = "form-control" type = "text" placeholder = "Search users...">
		</div>
		
		<form method = "post" action = "aff_permissions.php">
			<span class = "btn_yellow"> <input type = "submit" name = "button" class = "value_span6-2 value_span2 value_span1-2" value = "Save"/></span>
			
			<div class = "clear"></div>
			<div class = "white_box_x_scroll white_box manage_aff large_table value_span8  ">
				<table class = "table table-bordered table_01 tablesorter" id = "mainTable">
					<thead>
					
					<tr>
						<th class = "value_span9">ID</th>
						<th class = "value_span9">User Name</th>
						<?php
						$permissionNames = [];
						foreach ($affiliateList as $aff)
						{
							foreach ($aff as $columnName => $val)
							{
								if ($columnName !== "user_id" && $columnName !== "user_name")
								{
									echo "<th>$columnName</th>";
									$permissionNames[] = $columnName;
								}
							}
							break;
						}
						?>
					</tr>
					</thead>
					<tbody>
					<?php
					
					echo "<tr class='static' role='static'>";
					for ($i = -2; $i < count($permissionNames); $i++)
					{
						if ($i >= 0)
						{
							echo "<td><a  class='btn btn-sm btn-default' onclick='checkAllWithThisClass(\"{$permissionNames[$i]}\");' href='#'>Check All</a><a  style='margin-top:5px;' class='btn btn-sm btn-default' onclick='checkAllWithThisClass(\"{$permissionNames[$i]}\", false);'
href='#'>Un-Check
All</a></td>";
						}
						else
						{
							echo "<td></td>";
						}
					}
					echo "</tr>";
					
					
					foreach ($affiliateList as $affiliate => $columns)
					{
						echo "<tr>";
						foreach ($columns as $name => $data)
						{
							if ($name !== "user_name" && $name !== "user_id")
							{
								$hasPermission = ($data == 1) ? "checked" : "";
								echo "<td><input type='hidden'   name='{$columns["user_id"]}[$name]' value='0'>";
								echo "<input $hasPermission type='checkbox'  style='width:25px;!important; margin-right:20px;' name='{$columns["user_id"]}[$name]' value='$data' class='{$name}'></td>";
							}
							else
							{
								echo "<td>$data</td>";
							}
						}
						echo "</tr>";
					}
					?>
					</tbody>
				</table>
		
		</form>
	
	</div>
</div>
</div>
<!--right_panel-->

<script type = "text/javascript">
	
	
	function checkAllWithThisClass(className, checkTheBox = true) {
		$("." + className).each(function (i, checkBox) {
			
			if (checkTheBox) {
				if ($(checkBox).is(":checked") === false) {
					$(checkBox).click();
				}
			}
			else {
				if ($(checkBox).is(":checked") === true)
					$(checkBox).click();
			}
			
		});
	}
	
	$(document).ready(function () {
		$("#mainTable").tablesorter(
			{
				sortList: [[1, 0]],
				widgets: ['staticRow']
			});
		
		$("input[type='checkbox']").click(function (event) {
			if ($(this).val() === 1 || $(this).val() === "1") {
				$(this).val(0);
			}
			else
				$(this).val(1);
			console.log($(this).val());
		});
		
	});
</script>


<?php include 'footer.php'; ?>


