<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/2/2017
 * Time: 11:58 AM
 */


$section = "create-bonus";
require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("create_bonuses"))
{
	send_to("home.php");
}


if (isset($_POST["button"]))
{
	$bonusID = \LeadMax\TrackYourStats\User\Bonus::createBonus($_POST["name"], $_POST["salesRequired"], $_POST["payout"], $_POST["status"], isset($_POST["inheritable"]) ? $_POST["inheritable"] : 0);
	
	if (isset($_POST["replist"]))
	{
		\LeadMax\TrackYourStats\User\Bonus::assignUsersToBonus($bonusID, $_POST["replist"]);
	}
	
	$result = true;
}

if (isset($result))
{
	echo "<script type='text/javascript'>
              $.notify({

                title: 'Bonus',
                message: ' created successfully!'
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
			<div class = "heading_holder value_span9"><span class = "lft">Create Bonus</span></div>
			<div class = "white_box value_span8">
				
				<form action = "<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method = "post" id = "form"
					  enctype = "multipart/form-data">
					
					
					<div class = "left_con01">
						<p>
							<label class = "value_span9">Name</label>
							<input id = "title" name = "name" type = "text" value = "" required/>
						</p>
						<p>
							
							<label class = "value_span9">Sales Required</label>
							<input type = "number" name = "salesRequired" value = "0">
						</p>
						
						<p>
							<label class = "value_span9">Payout</label>
							<input type = "number" name = "payout" value = "0.00" step = "0.01">
						</p>
						
						<p>
							<label class = "value_span9">Status</label>
							<select name = "status">
								<option value = "1">Active</option>
								<option value = "0">In-Active</option>
							</select>
						</p>
						
						<p>
							<label for = "inheritable" class = "value_span9">Inheritable</label>
							<input type = "checkbox" class = "fixCheckBox" name = "inheritable" id = "inheritable" value="1">
						</p>
						
						<?php
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_admins"))
						{
							
							
							?>
							<p>
								<label>Admins</label>
								<span class = "small_txt value_span10">Assignned Admins</span>
								<select multiple onchange = "moveToSelect(this, 'assignedAdmins', 'unAssignedAdmins')" class = "form-control input-sm" id = "assignedAdmins"
										name = "replist[]">
								</select>
								
								<span class = "small_txt value_span10">UnAssigned Admins</span>
								<select multiple onchange = "moveToSelect(this, 'unAssignedAdmins', 'assignedAdmins')" class = "form-control input-sm "
										id = "unAssignedAdmins" name = "">
									
									
									<?php
									$admins = \LeadMax\TrackYourStats\User\User::selectAdmins()->fetchAll(PDO::FETCH_ASSOC);
									foreach ($admins as $admin)
										echo "<option value=\"{$admin["idrep"]}\">{$admin["user_name"]}</option>";
									?>
								</select>
							</p>
						
						<?php } ?>
					
					</div>
					
					<div class = "right_con01">
						<?php
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_managers"))
						{
							?>
							<p>
								<label>Managers</label>
								<span class = "small_txt value_span10">Assignned Managers</span>
								<select multiple onchange = "moveToSelect(this, 'assignedManagers', 'unAssignedManagers')" class = "form-control input-sm" id = "assignedManagers"
										name = "replist[]">
								</select>
								
								<span class = "small_txt value_span10">UnAssigned Managers</span>
								<select multiple onchange = "moveToSelect(this, 'unAssignedManagers', 'assignedManagers')" class = "form-control input-sm "
										id = "unAssignedManagers" name = "">
									
									
									<?php
									$managers = \LeadMax\TrackYourStats\User\User::selectOwnedManagers()->fetchAll(PDO::FETCH_ASSOC);
									foreach ($managers as $manager)
										echo "<option value=\"{$manager["idrep"]}\">{$manager["user_name"]}</option>";
									?>
								</select>
							</p>
						
						<?php } ?>
						
						
						<?php
						if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_affiliates"))
						{
							?>
							<p>
								<label>Affiliates</label>
								<span class = "small_txt value_span10">Assigned Affiliates</span>
								<select multiple onchange = "moveToSelect(this, 'assignedAffiliates', 'unAssignedAffiliates')" class = "form-control input-sm" id = "assignedAffiliates"
										name = "replist[]">
								</select>
								
								<span class = "small_txt value_span10">UnAssigned Affiliates</span>
								<select multiple onchange = "moveToSelect(this, 'unAssignedAffiliates', 'assignedAffiliates')" class = "form-control input-sm "
										id = "unAssignedAffiliates" name = "">
									
									
									<?php
									$affiliates = \LeadMax\TrackYourStats\User\User::selectAllOwnedAffiliates()->fetchAll(PDO::FETCH_ASSOC);
									foreach ($affiliates as $user)
										echo "<option value=\"{$user["idrep"]}\">{$user["user_name"]}</option>";
									?>
								</select>
							</p>
						
						<?php } ?>
					
					</div>
			</div>
			<span class = "btn_yellow"> <input id = "submitBtn" type = "submit" name = "button" class = "value_span6-2 value_span2 value_span1-2"
											   value = "Create" onclick = "return selectAllBonuses();"/></span>
		
		</div>
		</form>
	
	</div>
	
	
	<!--right_panel-->
	
	
	<script type = "text/javascript">
	
	
	</script>


<?php include 'footer.php'; ?>