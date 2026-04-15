<?php

include "header.php";


if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_GOD)
{
	send_to("home.php");
}


$assignments = new \LeadMax\TrackYourStats\Table\Assignments([
		'clickId' => 0
	]
);
$assignments->getAssignments();
$assignments->setGlobals();


$originalClickId = $clickId;

if (is_numeric($clickId) == false)
{
	$clickId = \LeadMax\TrackYourStats\Clicks\UID::decode($clickId);
}

$clickSearcher = new \LeadMax\TrackYourStats\Clicks\ClickSearcher($clickId);

$result = $clickSearcher->clickData()->fetchAll(PDO::FETCH_ASSOC);

?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table">
		<div class = "heading_holder">
			<span class = "lft value_span9">Click Searcher</span>
		
		</div>
		
		
		<div class = "form-group">
			<label for = "encodedClickId">Click ID</label>
			<input value = "<?= ($originalClickId !== 0) ? $originalClickId : "" ?>" class = "form-control" style = "width:250px" type = "text" name = "clickId" id = "clickId" placeholder = "Encoded or decoded...">
			<button onclick = "search()" class = "btn btn-default btn-sm" style = "margin-top: 5px;">Search</button>
		</div>
		
		
		<div class = "clear"></div>
		<div class = "white_box manage_aff large_table value_span8">
			<table class = "table table-bordered table-striped table_01 tablesorter" id = "mainTable">
				<thead>
				
				<tr>
					<td>ClickId</td>
					<td>Timestamp</td>
					<td>UserId</td>
					<td>OfferId</td>
					<td>Ip</td>
					<td>BrowserAgent</td>
					<td>ClickType</td>
				</tr>
				</thead>
				<tbody>
				
				<?php
				
				$output = new \LeadMax\TrackYourStats\Report\Formats\HTML();
				$output->output($result);
				
				?>
				</tbody>
			</table>
			
			<div style = "margin-top:20px;"></div>
			
			<label>Geo Data</label>
			<table class = "table table-bordered table-striped table_01 tablesorter" id = "mainTable">
				<thead>
				
				<tr>
					<td>ISO Code</td>
					<td>Sub Division</td>
					<td>City</td>
					<td>Postal</td>
					<td>Latitude</td>
					<td>Longitude</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<?php
					
					if ($result)
					{
						
						$geoData = \LeadMax\TrackYourStats\Clicks\ClickGeo::findGeo($result[0]["ip_address"]);
						if ($geoData)
						{
							
							foreach ($geoData as $col)
							{
								echo "<td>{$col}</td>";
							}
						}
						
					}
					?></tr>
				</tbody>
			</table>
			
			<div style = "margin-top:20px;"></div>
			
			<label>Stored Query String</label>
			<table class = "table table-bordered table-striped table_01 tablesorter" id = "mainTable">
				<thead>
				
				<tr>
					<td>Url</td>
					<?php
					for ($i = 1; $i <= 5; $i++)
						echo "<td>Sub {$i}</td>";
					?>
				</tr>
				</thead>
				<tbody>
				<tr>
					<?php
					$clickVars = $clickSearcher->clickVars()->fetch(PDO::FETCH_ASSOC);
					if ($clickVars)
					{
						foreach ($clickVars as $key => $data)
						{
							if ($key != "click_id") echo "<td>$data</td>";
						}
					}
					
					
					?>
				</tr>
				</tbody>
			</table>
			
			<div style = "margin-top:20px;"></div>
			
			<label>Conversion Data</label>
			<table class = "table table-bordered table-striped table_01 tablesorter" id = "mainTable">
				<thead>
				
				<tr>
					<td>Conversion Id</td>
					<td>Timestamp</td>
					<td>Paid</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<?php
					$conversion = \LeadMax\TrackYourStats\Clicks\Conversion::selectOne($clickId)->fetch(PDO::FETCH_OBJ);
					
					if ($conversion)
					{
						echo "<tr>";
						echo "<td>$conversion->id</td>";
						echo "<td>$conversion->timestamp</td>";
						echo "<td>$conversion->paid</td>";
						echo "</tr>";
					}
					
					?>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<!--right_panel-->

<script type = "text/javascript">
	
	function search() {
		var id = $("#clickId").val();
		window.location = "clicksearch.php?clickId=" + id;
	}
	
	$(document).ready(function () {
		$("#mainTable").tablesorter(
			{
				sortList: [[7, 1]],
				widgets: ['staticRow']
			});
	});
</script>

<?php include 'footer.php'; ?>




