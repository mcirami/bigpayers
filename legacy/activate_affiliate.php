<?php

require('header.php');

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("approve_affiliate_sign_ups"))
{
	send_to('home.php');
}

$assign = new \LeadMax\TrackYourStats\Table\Assignments(
	[
		'!id' => 0
	]
);

$create = new \LeadMax\TrackYourStats\User\Create();
$assign->getAssignments();
$assign->setGlobals();


$affiliate = \LeadMax\TrackYourStats\User\User::SelectOne($id);

if ($affiliate->status == 1 || $affiliate->lft != 0 || $affiliate->rgt != 0)
{
	\LeadMax\TrackYourStats\System\Notify::info("User already activated", "!", 3);
	send_to("home.php");
}


$create->dumpAssignablesToJavaScript();

$create->dumpPermissionsToJavascript();

\LeadMax\TrackYourStats\User\Create::activateAffiliate();

?>


<script type = "text/javascript" src = "js/aff.js"></script>
<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span
					class = "lft"> Activate Affiliate  </span></div>
		
		<div class = "white_box value_span8">
			
			<form action = "<?php htmlspecialchars($_SERVER['PHP_SELF']) . "?id={$id}"; ?>" method = "post" id = "form"
				  class = "form-horizontal" enctype = "multipart/form-data">
				
				<div class = "left_con01">
					<p>
						<label class = "value_span9">First Name</label>
						
						<input readonly type = "text" class = "form-control" name = "first_name" maxlength = "155" value = "<?= $affiliate->first_name ?>"
							   id = "first_name"/>
					</p>
					<p>
						<label class = "value_span9">Last Name</label>
						
						<input readonly type = "text" class = "form-control" name = "last_name" maxlength = "155" value = "<?= $affiliate->last_name ?>"
							   id = "last_name"/>
					</p>
					<p>
						<label class = "value_span9">Email</label>
						
						<input readonly type = "text" class = "form-control input-sm" name = "email" maxlength = "155" value = "<?= $affiliate->email ?>"
							   id = "email"/>
					</p>
					<p>
						<label class = "value_span9">Company</label>
						<!-- TODO Link Referrer Payout -->
						<input readonly type = "text" class = "form-control" name = "company_name" minlength = "5" maxlength = "255" value = "<?= $affiliate->company_name ?>"
							   value = "" id = "company_name"/>
					</p>
					<p>
						<label class = "value_span9">Skype</label>
						<!-- TODO Link Referrer Payout -->
						<input readonly type = "text" class = "form-control" name = "skype" minlength = "5" maxlength = "255" value = "<?= $affiliate->skype ?>"
							   value = "" id = "skype"/>
					</p>
				
				
				</div>
				<div class = "right_con01">
					<p>
						<label class = "value_span9">Username</label>
						
						<input readonly type = "text" class = "form-control" name = "user_name" maxlength = "155" value = "<?= $affiliate->user_name ?>"
							   id = "user_name"/>
					</p>
					
					
					<p>
						<label class = "value_span9">Status</label>
						<select class = "form-control input-sm " id = "status" name = "status">
							<option value = "1" selected>Active</option>
							;
							<option value = "0" disabled>Disabled</option>
							;
						</select>
					</p>
					<p>
						<label class = "value_span9">Privileges</label>
						<?php
						echo "<input onclick=\"manager();appendAffiliate();\" class=\"fixCheckBox\" type=\"radio\" name=\"priv\" value=\"" . \App\Privilege::ROLE_AFFILIATE . "\">Affiliate ";
						?>
					
					
					</p>
					<p>
						<label class = "value_span9">Assign To</label>
						<select required class = "form-control input-sm " id = "referrer_repid" name = "referrer_repid">

						</select>
					</p>

					<?php
					if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_referrals"))
					{
						echo "<p id=\"referralP\" style=\"display:none;\">
					                        <label  class=\"value_span9\">Referrals</label>
					                        <input class=\"fixCheckBox\" type=\"checkbox\" id=\"referralCheckBox\"  name=\"referralCheckBox\"> Enable
					                    <p id=\"referralForm\" style=\"display:none;\">";


						echo " <label style=\"font-size:12px;\" for=\"referralSelectBox\">Referrer</label>
					                        <select class=\"form-control\" id=\"referralSelectBox\" name=\"referralSelectBox\" disabled required>
					                            ";

						\LeadMax\TrackYourStats\User\Referrals::printAffiliatesToSelectBox();

						echo "</select>
					
					                        <label style=\"font-size:12px;\"  for=\"start_date\">Start Date</label>
					                        <input id=\"start_date\" name=\"start_date\" type=\"date\" disabled required>
					
					                        <label style=\"font-size:12px;\"  for=\"end_date\">End Date (Empty for Indefinite)</label>
					                        <input id=\"end_date\" name=\"end_date\" type=\"date\"  disabled>
					
					
					
					                        <label style=\"font-size:12px;\"  for=\"referral_type\">Flat Fee / Percentage</label>
					                        <select id=\"referral_type\" name=\"referral_type\" class=\"form-control\" disabled required>
					                            <option value=\"flat\" id=\"flat_fee\">Flat Fee</option>
					                            <option value=\"percentage\" id=\"percentage\">Percentage</option>
					                        </select>
					
					
					                        <label style=\"font-size:12px;\"  for=\"amount\">Amount / Percentage</label>
					                        <input id=\"amount\" name=\"amount\" type=\"number\" value=\"0\" disabled required>
					
					
					                    </p>";

					}

					?>


					</p>

					<p id = "permissionsP">


					</p>
					<span class = "btn_yellow"> <input type = "submit" name = "button"
													   class = "value_span6-2 value_span2 value_span4"
													   value = "Activate"/></span>
					<span class = "btn_yellow" style = "margin-left:2%;"> <a onclick = "history.go(-1);"
																			 class = "value_span6-2 value_span2 value_span4"
						>Cancel</a></span>

				</div>
			
			</form>
			
			
			<script type = "text/javascript">
				
				$("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
				$("#end_date").datepicker({dateFormat: 'yy-mm-dd'});
				
				//load datepickers..
				$(function () {
					$("#start_date").datepicker({dateFormat: 'yy-mm-dd'});
					$("#end_date").datepicker({dateFormat: 'yy-mm-dd'});
					
				});
				
				$(document).ready(function () {
					$("#referralCheckBox").change(function () {
						
						$("#referralCheckBox").attr("disabled", "disabled");
						
						var capForm = $("#referralForm");
						
						if (capForm.css("display") === "none") {
							$("#referralSelectBox").removeAttr("disabled");
							$("#referral_type").removeAttr("disabled");
							$("#amount").removeAttr("disabled");
							$("#start_date").removeAttr("disabled");
							$("#end_date").removeAttr("disabled");
							capForm.slideDown('slow', function () {
								$("#referralCheckBox").removeAttr("disabled");
								
								
							});
						}
						
						else {
							$("#referralSelectBox").prop("disabled", true);
							$("#referral_type").prop("disabled", true);
							$("#amount").prop("disabled", true);
							$("#start_date").prop("disabled", true);
							$("#end_date").prop("disabled", true);
							
							capForm.slideUp('slow', function () {
								$("#referralCheckBox").removeAttr("disabled");
							});
							
						}
						
						
					});
					
					if (typeof cap_enabled !== 'undefined' && cap_enabled == true)
						$("#enable_cap").click();
					
				});
			
			
			</script>
			
			
			<script>
				
				function enableDisable() {
					$("#referralSelectBox").prop("disabled", !$('#referralCheckBox').prop('checked'));
					$("#referral_type").prop("disabled", !$('#referralCheckBox').prop('checked'));
					$("#amount").prop("disabled", !$('#referralCheckBox').prop('checked'));
					$("#start_date").prop("disabled", !$('#referralCheckBox').prop('checked'));
					$("#end_date").prop("disabled", !$('#referralCheckBox').prop('checked'));
					
				}
				
				$("#referralCheckBox").change(enableDisable);
				
			</script>
		</div>
	
	
	</div>
	
	
	<!--right_panel-->
	
	<?php include "footer.php"; ?>
	
	
	<script type = "text/javascript">
		
		
		// A $( document ).ready() block.
		$(document).ready(function () {

			if(document.querySelector(".fixCheckBox")) {
				$("input[type='radio']").click();
				console.log("ready!");
			}
		});
	
	
	</script>

