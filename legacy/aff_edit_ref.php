<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/30/2017
 * Time: 1:04 PM
 */

require 'header.php';

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_referrals"))
{
	send_to("home.php");
}


echo "<script type=\"text/javascript\">";
echo "var successful = false;";
if (isset($_POST["referrer"]) && isset($_POST["affid"]))
{
	if (\LeadMax\TrackYourStats\User\Referrals::updateReferral($_POST["referrer"], $_POST["affid"], $_POST))
	{
		echo " successful = true;";
	}
}
else
{
	$_POST["referrer"] = -1;
	$_POST["affid"]    = -1;
}

echo "</script>";

$assign = new \LeadMax\TrackYourStats\Table\Assignments(
	[
		"!affid" => -1
	]
);

$assign->getAssignments();
$assign->setGlobals();

// checks if they have this affiliate assigned to them
if (!\LeadMax\TrackYourStats\User\User::hasAffiliate($affid))
{
	send_to("home.php");
}


if (isset($_GET["aff_id"]) && isset($_GET["action"]) && $_GET["action"] == "del")
{
	\LeadMax\TrackYourStats\User\Referrals::deleteReferralStructure($affid, $_GET["aff_id"]);
	send_to("aff_edit_ref.php?affid={$affid}");
}

$report = new \LeadMax\TrackYourStats\User\Referrals($affid);


?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer white_box_x_scroll">
		<div class = "heading_holder value_span9"><span
					class = "lft"><?php echo $report->selectedAffiliate->user_name ?>'s Referrals</span>
		</div>
		
		<div class = "white_box value_span8 ">
			
			<!-- LEFT CON -->
			<div class = "left_con01 ">
				<a class = "btn btn-default btn-sm" style = "margin-bottom: 5px;" href = "/add_referral.php?id=<?= $affid ?>"><img src = "images/icons/add.png">Add Referral for <?= $report->selectedAffiliate->user_name ?></a>
				<table id = "referralTable" class = " table table-bordered table-stripped table_01" style = "min-width:500px !important;">
					
					<?php
					$report->printReferrersToTable();
					?>
				</table>
			
			</div>
			<!-- RIGHT CON -->
			<div class = "right_con01">
				
				<form action = "aff_edit_ref.php?affid=<?= $affid ?>" method = "POST">
					<p id = "referralP">
						
						<label class = "value_span9">Edit Referral</label>
						
						<input id = "referrer" name = "referrer" type = "hidden">
						<input id = "affid" name = "affid" type = "hidden">
					
					
					<div class = "form-group">
						<label style = "font-size:12px;" for = "start_date">Start Date</label>
						<input class = "form-control" id = "start_date" name = "start_date" type = "date" disabled required>
					
					</div>
					<div class = "form-group">
						
						<label style = "font-size:12px;" for = "end_date">End Date (Empty for Indefinite)</label>
						<input class = "form-control" id = "end_date" name = "end_date" type = "date" disabled>
					</div>
					
					<div class = "form-group">
						
						<label style = "font-size:12px;" for = "referral_type">Flat Fee / Percentage</label>
						<select id = "referral_type" name = "referral_type" class = "form-control" disabled required>
							<option value = "flat" id = "flat_fee">Flat Fee</option>
							<option value = "percentage" id = "percentage">Percentage</option>
						</select>
					</div>
					
					<div class = "form-group">
						
						<label style = "font-size:12px;" for = "amount">Amount / Percentage</label>
						<input class = "form-control" id = "amount" name = "amount" type = "number" value = "0" disabled required>
					</div>
					<div class = "form-group">
						
						<label style = "font-size:12px;" for = "amount">Active</label>
						<select id = "is_active" name = "is_active" class = "form-control" disabled required>
							<option value = "active" id = "active">Active</option>
							<option value = "unactive" id = "unactive">Un-active</option>
						</select>
					</div>
					
					<div class = "col-sm-4">
						
						<button type = "submit" class = "btn btn-success" id = "saveBtn" disabled><img
									src = "/images/icons/table_save.png">Save
						</button>
					</div>
				
				</form>
			
			</div>
		
		
		</div>
	
	
	</div>


</div>

<?php
$report->dumpReferralsToJavascript();
?>

<script type = "text/javascript">
	
	$("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
	$("#end_date").datepicker({dateFormat: 'yy-mm-dd'});
	
	//load datepickers..
	$(function () {
		$("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
		$("#end_date").datepicker({dateFormat: 'yy-mm-dd'});
		
	});
	
	public
	
	function deleteReferral(affiliateID) {
		if (confirm("Are you sure you want to delete this referral structure?")) {
			window.location = "aff_edit_ref.php?action=del&affid=<?=$affid?>&aff_id=" + affiliateID;
		}
	}
	
	function greatSuccess(ref, aff) {
		loadRef(ref, aff);
		$.notify({
				
				title: "Success!",
				message: "Saved referral settings."
			}, {
				placement: {
					from: "top",
					align: "center"
				},
				type: "success",
				animate: {
					enter: 'animated fadeInDown',
					exit: 'animated fadeOutUp'
				},
			}
		);
		
		
	}
	
	if (successful == true)
		greatSuccess(<?=$_POST["referrer"]?>, <?=$_POST["affid"]?> );
	
	function loadRef(referrerID, affid) {
		enableForm();
		
		$("#referrer").val(referrerID);
		$("#affid").val(affid);
		
		ref = refs[affid];
		
		$("#start_date").val(ref.start_date);
		$("#end_date").val(ref.end_date);
		$("#referral_type").val(ref.referral_type);
		$("#amount").val(ref.payout);
		
		if (ref.is_active === 1)
			$("#is_active").val("active");
		else
			$("#is_active").val("unactive");
		
		
	}
	
	
	function enableForm() {
		$("#referralSelectBox").prop("disabled", false);
		$("#referral_type").prop("disabled", false);
		$("#amount").prop("disabled", false);
		$("#start_date").prop("disabled", false);
		$("#end_date").prop("disabled", false);
		$("#is_active").prop("disabled", false);
		$("#saveBtn").prop("disabled", false);
	}

</script>