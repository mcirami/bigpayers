<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/5/2018
 * Time: 5:44 PM
 */
require('header.php');


if (\LeadMax\TrackYourStats\System\Session::permissions()->can("approve_affiliate_sign_ups") == false)
{
	send_to('home.php');
}

$users = \LeadMax\TrackYourStats\User\AffiliateSignUp::queryFetchPendingAffiliates()->fetchAll(PDO::FETCH_OBJ);

?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table">
		<div class = "heading_holder">
			<span class = "lft value_span9">Pending Agents</span>
		
		</div>
		
		
		<div class = "clear"></div>
		<div class = "white_box manage_aff large_table value_span8">
			<table class = "table table-bordered table-striped table_01" id = "mainTable">
				<thead>
				
				<tr>
					
					<th class = "value_span9">User ID</th>
					<th class = "value_span9">Username</th>
					<th class = "value_span9">First Name</th>
					<th class = "value_span9">Last Name</th>
					<th class = "value_span9">Email</th>
					<th class = "value_span9">Skype</th>
					<th class = "value_span9">Company Name</th>
					<th class = "value_span9">Timestamp</th>
					<th class = "value_span9">Actions</th>
				
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($users as $user)
				{
					echo "<tr>";
					echo "<td>{$user->idrep}</td>";
					echo "<td>{$user->user_name}</td>";
					echo "<td>{$user->first_name}</td>";
					echo "<td>{$user->last_name}</td>";
					echo "<td>{$user->email}</td>";
					echo "<td>{$user->skype}</td>";
					echo "<td>{$user->company_name}</td>";
					echo "<td>{$user->rep_timestamp}</td>";
					echo "<td><a target='_blank' class='btn btn-sm value_span5-1 value_span2 value_span4' href='activate_affiliate.php?id={$user->idrep}'>Activate</a></td>";
					echo "</tr>";
				}
				?>
				</tbody>
			</table>
		
		
		</div>
	</div>
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


