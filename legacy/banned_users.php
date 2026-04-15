<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/15/2018
 * Time: 9:55 AM
 */

include "header.php";

if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::BAN_USERS) == false)
{
	send_to("home.php");
}


$repo = new \LeadMax\TrackYourStats\Report\Repositories\BannedUsersRepository(\LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance());

$reporter = new \LeadMax\TrackYourStats\Report\Reporter($repo);


?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table ">
		<div class = "heading_holder">
			<span class = "lft value_span9">Banned Users</span>
		
		</div>
		
		<div class = "clear"></div>
		<div class = "white_box_x_scroll white_box manage_aff large_table value_span8  ">
			<table class = "table table-bordered table_01 tablesorter" id = "mainTable">
				<thead>
				
				<tr>
					<th class = "value_span9">User ID</th>
					<th class = "value_span9">User Name</th>
					<th class = "value_span9">Ban Date</th>
					<th class = "value_span9">Ban Expires</th>
					<th class = "value_span9">Reason</th>
					<th class = "value_span9">Status</th>
					<th class = "value_span9">Actions</th>
				</tr>
				</thead>
				<?php
				$reporter->between(false, false, new \LeadMax\TrackYourStats\Report\Formats\HTML());
				?>
				
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>

