<?php
$section = "create-notification";
require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("create_notifications"))
{
	send_to("home.php");
}

$create = new \LeadMax\TrackYourStats\System\Notifications(\LeadMax\TrackYourStats\System\Session::userID());
$result = $create->checkPostAndCreate();


$myAffiliates = \LeadMax\TrackYourStats\User\User::selectAllOwnedAffiliates()->fetchAll(PDO::FETCH_ASSOC);

if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_admins"))
{
	$admins = \LeadMax\TrackYourStats\User\User::selectAllAdmins();
}

if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers"))
{
	$managers = \LeadMax\TrackYourStats\User\User::selectAllManagers();
}


if ($result)
{
	echo "<script type='text/javascript'>
              $.notify({

                title: 'Notification',
                message: ' sent successfully!'
            }, {
            placement: {
                from: 'top',
                align: 'center'
            },
                type: 'info',
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            }
        );
            

</script>";
}


?>
	
	<!--right_panel-->
	<div class = "right_panel">
		<div class = "white_box_outer">
			<div class = "heading_holder value_span9"><span class = "lft">Create Notification</span></div>
			<div class = "white_box value_span8">
				
				<form action = "<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method = "post" id = "form"
					  enctype = "multipart/form-data">
					
					
					<div class = "left_con01">
						<p>
							<label class = "value_span9">Title</label>
							<input id = "title" name = "title" type = "text" value = "" required/>
						</p>
						<p>
							
							<label class = "value_span9">Body</label>
							<textarea name = "body" class = "input" style = "width:96%; height:100px;"></textarea>
						</p>
						
						
						<p>
							<label>Options</label>
							<input class = "fixCheckBox" id = "sendEmails" type = "checkbox" name = "sendEmails" value = "true"> Send Emails <br/>
						
						</p>
						
						<label>Recipients:</label>
						<button class = "btn btn-sm btn-default btn-sm-margin-bot" onclick = "clickCheckBoxesInTable('recipients'); return false;">Move All</button>
						<table id = "recipients" class = "table table-sm table-bordered table_01 tablersorter" style="min-width:150px;">
							<thead>
							<tr>
								<th>User</th>
								<th>Type</th>
								<th>Action</th>
							</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					
					</div>
					
					<div class = "right_con01">
						
						<label>Users:</label>
						<div class = "form-group searchDiv" style = "margin-bottom: 10px;">
							
							<input id = "searchBox" onkeyup = "searchTable()" class = "form-control" type = "text" placeholder = "Search users...">
							<select id = "selectUserType" class = "form-control" style = "margin-top:10px;">
								<option value = "All">All</option>
								<?php
								if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_admins")) echo "<option value=\"Admin\">Admins</option>";
								if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers")) echo "<option value=\"Manager\">Managers</option>";
								?>
								<option value = "Affiliate">Affiliates</option>
							</select>
						</div>
						
						<button class = "btn btn-sm btn-default btn-sm-margin-bot" onclick = "clickCheckBoxesInTable('mainTable'); return false;">Move All</button>
						<table id = "mainTable" class = " table  verySmallTable table-bordered table_01 tablesorter" style = "min-width:200px; !important;">
							<thead>
							<tr>
								<th>User</th>
								<th>Type</th>
								<th>Action</th>
							</tr>
							</thead>
							<tbody>
							<?php
							
							if (isset($admins))
							{
								
								foreach ($admins as $admin)
								{
									echo "<tr>";
									echo "<td>{$admin["user_name"]}</td>";
									echo "<td>Admin</td>";
									echo "<td><input  style='width:25px;' type='checkbox' name='userList[]' value='{$admin["idrep"]}'></td>";
									echo "</tr>";
									
								}
							}
							
							
							if (isset($managers))
							{
								
								foreach ($managers as $manager)
								{
									echo "<tr>";
									echo "<td>{$manager["user_name"]}</td>";
									echo "<td>Manager</td>";
									echo "<td><input  style='width:25px;' type='checkbox' name='userList[]' value='{$manager["idrep"]}'></td>";
									echo "</tr>";
									
								}
							}
							
							foreach ($myAffiliates as $affiliate)
							{
								echo "<tr>";
								echo "<td>{$affiliate["user_name"]}</td>";
								echo "<td>Affiliate</td>";
								echo "<td><input  style='width:25px;' type='checkbox' name='userList[]' value='{$affiliate["idrep"]}'></td>";
								echo "</tr>";
							}
							?>
							</tbody>
						</table>
					
					
					</div>
			</div>
			<span class = "btn_yellow"> <input id = "submitBtn" type = "submit" name = "button" class = "value_span6-2 value_span2 value_span1-2"
											   value = "Send" onclick = "return selectAll();"/></span>
		
		</div>
	</div>
	
	
	<!--right_panel-->
	
	
	<script type = "text/javascript">
		
		
		function showThisType(type) {
			$("#mainTable tr").each(function (i, tr) {
				$(tr).find('td').each(function (k, td) {
						if (type === "All") {
							$(tr).css('display', '');
						}
						else {
							
							if (k === 1) {
								if ($(td).html() === type)
									$(tr).css("display", "");
								else
									$(tr).css('display', 'none');
							}
						}
					}
				);
				
			});
		}
		
		
		function clickCheckBoxesInTable(tableId) {
			$("#" + tableId).find("tr").each(function (i, tr) {
				
				console.log($(tr).css("display"));
				if ($(tr).css("display") === "table-row") {
					
					$(tr).find("td").each(function (k, td) {
						$(td).find("input[type='checkbox']").click();
					});
				}
			});
		}
		
		
		$("#checkAll").click(function () {
			$('input:checkbox').not(this).not("#sendEmails").prop('checked', this.checked);
			
		});
		
		$(document).ready(function () {
			
			$("#mainTable").tablesorter(
				{
					sortList: [[1, 0]]
				});
			
			$("#recipients").tablesorter(
				{
					sortList: [[1, 0]]
				});
			
			
			$("#selectUserType").on('change', function () {
				showThisType(this.value);
				$("#searchBox").val("");
			});
			
			
			$("#recipients").on("click", "input[type='checkbox']", function () {
				var tr = $(this).closest("tr").remove().clone();
				var tr = $(this).closest("tr").remove().clone();
				$("#mainTable tbody").append(tr);
				showThisType($("#selectUserType").val());
			});
			
			$("#mainTable").on("click", "input[type='checkbox']", function () {
				var tr = $(this).closest("tr").remove().clone();
				$("#recipients tbody").append(tr);
				showThisType($("#selectUserType").val());
			});
			
			$('#submitBtn').click(function () {
				checked = $("input[type=checkbox]:checked").length;
				
				if (!checked) {
					alert("You must select at least one user group.");
					return false;
				}
				
			});
		});
	
	</script>


<?php include 'footer.php'; ?>