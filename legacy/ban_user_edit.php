<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/15/2018
 * Time: 10:50 AM
 */

include "header.php";

if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::BAN_USERS) == false)
{
	send_to("home.php");
}

if (isset($_POST["user_id"]))
{
	if (is_numeric($_POST["user_id"]) && \LeadMax\TrackYourStats\User\User::userOwnsUser(\LeadMax\TrackYourStats\System\Session::userID(), $_POST["user_id"]))
	{
		\LeadMax\TrackYourStats\User\BanUser::updateBan($_POST["user_id"], $_POST["expires"], $_POST["reason"], $_POST["status"]);
		send_to("ban_user_edit.php?uid={$_POST["user_id"]}");
	}
	else
	{
		send_to("home.php");
	}
}

if (isset($_GET["uid"]))
{
	if (is_numeric($_GET["uid"]) && \LeadMax\TrackYourStats\User\User::userOwnsUser(\LeadMax\TrackYourStats\System\Session::userID(), $_GET["uid"]))
	{
		$userId = $_GET["uid"];
	}
	else
	{
		send_to("home.php");
	}
}
else
{
	send_to("home.php");
}

$ban = \LeadMax\TrackYourStats\User\BanUser::getBannedUserQuery($userId)->fetch(PDO::FETCH_OBJ);

$user = \LeadMax\TrackYourStats\User\User::SelectOne($userId);

?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		
		<div class = "heading_holder value_span9">
			<span class = "lft"> Change Ban Settings for <?= $user->user_name ?></span>
		</div>
		
		<div class = "white_box value_span8">
			
			<form action = "ban_user_edit.php" method = "post" enctype = "multipart/form-data">
				
				<input type = "hidden" name = "user_id" value = "<?= $user->idrep ?>">
				<div class = "left_con01">
					
					<p>
						<label for = "expires">Expiration Date:</label>
						<input type = "text" id = "expires" name = "expires" value = "<?= $ban->expires ?>">
					</p>
					
					<p>
						<label for = "status">Status:</label>
						<select id = "status" name = "status">
							<?php $active = ($ban->status === 1) ? "selected" : "";
							$inActive     = ($ban->status === 0) ? "selected" : "";
							?>
							<option <?= $active ?> value = "1">Active</option>
							<option <?= $inActive ?> value = "0">In-Active</option>
						</select>
					</p>
					
					<p>
						<label for = "reason">Reason:</label>
						<textarea id = "reason" name = "reason" style = "min-width:300px; min-height:150px;"><?= $ban->reason ?></textarea>
					</p>
					
					
					<button class = "btn btn-danger">Update</button>
				
				</div>
			</form>
		</div>
	</div>
</div>

<script type = "text/javascript">
	
	$(document).ready(function () {
		$("#expires").datepicker({dateFormat: 'yy-mm-dd'});
	});

</script>

<? include "footer.php"; ?>


