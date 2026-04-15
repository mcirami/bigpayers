<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/25/2017
 * Time: 3:28 PM
 */

$section = "offer-urls";

require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_urls"))
{
	send_to("home.php");
}


$offerURLs = new \LeadMax\TrackYourStats\Offer\URLs(\LeadMax\TrackYourStats\System\Company::loadFromSession());

$urls = $offerURLs->getOfferUrls()->fetchAll(PDO::FETCH_ASSOC);


?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table ">
		<div class = "heading_holder">
			<span class = "lft value_span9">Offer URLs</span>
			<a href = "add_offer_url.php" class = "btn btn-sm btn-default">Create New</a>
		
		</div>
		
		<div class = "white_box_x_scroll white_box manage_aff large_table value_span8 ">
			<table class = "table table-bordered table_01" id = "mainTable">
				<thead>
				
				<tr>
					<th class = "value_span9">Offer URL</th>
					<th class = "value_span9">Status</th>
					<th class = "value_span9">Created On</th>
					<th class = "value_span9">Actions</th>
				
				
				</tr>
				</thead>
				<tbody>
				<?php
				
				foreach ($urls as $url)
				{
					echo "<tr>";
					echo "<td>{$url["url"]}</td>";
					if ($url["status"] == 1)
					{
						echo "<td><span style='color:green'>ACTIVE</span>";
					}
					else
					{
						echo "<td><span style='color:red'>IN-ACTIVE</span>";
					}
					
					echo "<td>" . \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $url["timestamp"])->toFormattedDateString() . "</td>";
					
					echo "<td>";
					echo "<a class='btn btn-default btn-sm' href='edit_offer_url.php?id={$url["id"]}'>Edit</a>";
					echo "</td>";
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
				sortList: [[2, 1]],
				widgets: ['staticRow']
			});
	});
</script>

<?php include 'footer.php'; ?>


