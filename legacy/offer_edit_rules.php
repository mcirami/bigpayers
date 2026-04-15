<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/15/2017
 * Time: 4:34 PM
 */

 use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Illuminate\Support\Facades\Log;
$section = "offers-edit-rules";
require('header.php');



if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_rules"))
{
send_to("home.php");
}

if (!isset($_GET["offid"]))
{
	send_to("home.php");
}


//verify User has this offer


$offid = filter_var($_GET["offid"], FILTER_SANITIZE_NUMBER_INT);


$selectedOffer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($offid)->fetch(PDO::FETCH_OBJ);

$rules = new \LeadMax\TrackYourStats\Offer\Rules($offid);

$offerView = new \LeadMax\TrackYourStats\Offer\View(\LeadMax\TrackYourStats\System\Session::userType());

$activeCap = false;
$capAmount = 0;

foreach ($rules->rules as $rule) {

	if ($rule["type"] == "device") {
		$activeCap = $rule["cap_status"];
		$capAmount = $rule["cap"];
	}
}

?>
	
	
	<!-- Geo Modal -->
	<div class = "modal " id = "geoModal" tabindex = "-1" role = "dialog" aria-labelledby = "geoModalLabel">
		<div class = "modal-dialog" role = "document">
			<div class = "modal-content">
				<div class = "modal-header">
					<button type = "button" class = "close" data-dismiss = "modal"
							aria-label = "Close"><span
								aria-hidden = "true">&times;</span></button>
					<h4 style="float: left;" class = "modal-title" id = "geoRuleTitle">New Geo Rule</h4>
				</div>
				<div class = "modal-body ">
					<div class = "row">
						
						<div class = "col-md-6 ">
							<label class = "control-label">Country List:</label>
							
							<table id = countryList"
								   class = "table table-sm table-bordered table-responsive table-striped form-control  "
								   style = "height:250px;  min-width:0 !important;">
								<thead>
								<tr>
									<th>Country</th>
									<th>Action</th>
								</tr>
								</thead>
								<tbody id = "countryListBody">
								<?php \LeadMax\TrackYourStats\Offer\Rules\Geo::printCountriesAsTable(); ?>
								
								</tbody>
							
							</table>
							<input type = "text" id = "searchCountryList" placeholder = "Search countries..." style = "width:100%;">
						</div>
						
						
						<div class = "col-md-6 ">
							<label class = "control-label">Items:</label>
							
							<table id = "toAdd"
								   class = "table table-sm table-bordered table-responsive table-striped form-control  "
								   style = "height:250px;  min-width:0 !important; ">
								<thead>
								<tr>
									<th>Country</th>
									<th>Action</th>
									<th>Caps</th>
								</tr>
								</thead>
								<tbody>
								
								</tbody>
							
							</table>
						
						
						</div>
					
					</div>
					
					<div class = "row">
						
						<div class = "form-group">
							<input id = "geoIsAllowed" type = "checkbox"
								   style = "width:15px;height:15px;">
							<span>Countries in <b>Items</b> list will <b>NOT</b> be allowed.</span>
						
						</div>
						<div class = "form-group">
							<input checked id = "geoIsActive" type = "checkbox"
								   style = "width:15px;height:15px;">
							<span>Active</span>
						</div>
						<input type = "hidden" id = "offerID" value = "<?= $offid ?>">
						<input type = "hidden" id = "geoRuleID" value = "">
						
						
						<div class = "form-group">
							<label for = "geoRuleName">Rule Name:</label>
							<input type = "text" id = "geoRuleName">
						</div>
						
						<div class = "form-group">
							<label style = "margin-top:10px;" for = "geoRedirectOffer">Redirect Offer:</label>
							<?php $offerView->printToSelectBox("geoRedirectOffer"); ?>
						</div>
					</div>
				</div>
				<div class = "modal-footer" style = "position:unset;">
					<button id = "geoCancelButton" type = "button" class = "btn btn-default"
							data-dismiss = "modal">
						Cancel
					</button>
					<button id = "geoCreateButton" type = "button" class = "btn btn-primary">Create
					</button>
					<button id = "geoUpdateButton" type = "button" class = "btn btn-primary"
							style = "display:none;">
						Update
					</button>
				
				</div>
			
			
			</div>
		
		
		</div>
	</div>
	
	
	<!-- Device Modal -->
	<div class = "modal " id = "deviceModal" tabindex = "-1" role = "dialog" aria-labelledby = "deviceModal">
		<div class = "modal-dialog" role = "document">
			<div class = "modal-content">
				<div class = "modal-header">
					<button type = "button" class = "close" data-dismiss = "modal"
							aria-label = "Close"><span
								aria-hidden = "true">&times;</span></button>
					<h4 style="float: left;" class = "modal-title" id = "deviceRuleTitle">New Device Rule</h4>
				</div>
				
				<div class = "modal-body ">
					<div class = "row">
						
						<div class = "col-md-6 ">
							<label class = "control-label">Device List:</label>
							
							<table id = deviceList"
								   class = "table table-sm table-bordered table-responsive table-striped form-control  "
								   style = "height:250px;  min-width:0 !important; ">
								<thead>
								<tr>
									<th>Device</th>
									<th>Action</th>
								
								</tr>
								</thead>
								<tbody id = "deviceListBody">
								
								<tr id = "desktop">
									<td>Desktop</td>
									<td><a id = "_desktop" onclick = "addDevice('desktop');" href = "javascript:void(0);"><img id = "desktop_img" src = "images/icons/add.png"></a></td>
								</tr>
								
								<tr id = "mobile">
									<td>Mobile</td>
									<td><a id = "_mobile" onclick = "addDevice('mobile');" href = "javascript:void(0);"><img id = "mobile_img" src = "images/icons/add.png"></a></td>
								</tr>
								
								
								</tbody>
							
							</table>
						</div>
						
						
						<div class = "col-md-6 ">
							<label class = "control-label">Items:</label>
							
							<table id = "deviceToAdd"
								   class = "table table-sm table-bordered table-responsive table-striped form-control  "
								   style = "height:250px; min-width:0 !important;">
								<thead>
								<tr>
									<th>Device</th>
									<th>Action</th>
								
								</tr>
								</thead>
								<tbody>
								
								
								</tbody>
							
							</table>
						
						</div>
					
					</div>
					
					<div class = "row">
						
						<div class = "form-group">
							<input id = "deviceIsAllowed" type = "checkbox" style = "width:15px;height:15px;">
							<span>Devices in <b>Items</b> list will <b>NOT</b> be allowed.</span>
						
						</div>
						<div class = "form-group">
							<input checked id = "deviceIsActive" type = "checkbox"
								   style = "width:15px;height:15px;">
							<span>Active</span>
						</div>
						<input type = "hidden" id = "offerID" value = "<?= $offid ?>">
						<input type = "hidden" id = "deviceRuleID" value = "">
						
						
						<div class = "form-group">
							<label for = "deviceRuleName">Rule Name:</label>
							<input type = "text" id = "deviceRuleName">
						</div>
						
						<div class = "form-group">
							<label style = "margin-top:10px;" for = "deviceRedirectOffer">Redirect Offer:</label>
							<?php $offerView->printToSelectBox("deviceRedirectOffer"); ?>
						</div>
						<div class = "form-group">
							<input <?php if ($activeCap) { echo "checked"; } ?> id = "capIsActive" type = "checkbox"
									style = "width:15px;height:15px;">
								<span>Enable Cap</span>
						</div>
						<div class = "form-group">
							<label for = "deviceCap">Cap:</label>
							<input type = "text" id = "deviceCap" value=<?php echo $capAmount; ?>>
						</div>
					
					</div>
				</div>
				
				<div class = "modal-footer" style = "position:unset;">
					<button id = "deviceCancelButton" type = "button" class = "btn btn-default"
							data-dismiss = "modal">
						Cancel
					</button>
					<button id = "deviceCreateButton" type = "button" class = "btn btn-primary">Create
					</button>
					<button id = "deviceUpdateButton" type = "button" class = "btn btn-primary"
							style = "display:none;">
						Update
					</button>
				
				</div>
			</div>
		</div>
	</div>
	
	<!--right_panel-->
	<div class = "right_panel">
		<div class = "white_box_outer white_box_x_scroll">
			<div class = "heading_holder">
				<span class = "lft value_span9">Edit Rules for <?= $selectedOffer->offer_name ?> - <?= $offid ?></span>
			
			</div>
			
			
			<div class = "clear"></div>
			
			
			<div class = " white_box value_span8">
				
				<div class = "left_con01 white_box_x_scroll">
					
					
					<p>
						
						<label class = "form-group">Rules</label>
						<!-- Geo Modal trigger modal -->
						<button type = "button" class = "btn btn-default btn-sm " data-toggle = "modal"
								data-target = "#geoModal">
							Add Geo Rule
						</button>
						
						<!-- Geo Modal trigger modal -->
						<button type = "button" class = "btn btn-default btn-sm " data-toggle = "modal"
								data-target = "#deviceModal">
							Add Device Rule
						</button>
						
						<a class = "btn btn-sm btn-default" href = "create_none_unique.php?id=<?= $offid ?>">Add None Unique Rule</a>
					
					</p>
					<table id = "rules"
						   class = "table table-sm table-sm table-condensed table-bordered table-stripped table-hover table_01">
						
						<thead>
						<th>Rule Name</th>
						<th>Type</th>
						<th>Allow/Deny Items</th>
						<th>Redirect Offer</th>
						<th>Active</th>
						<th>Actions</th>
						
						</thead>
						
						
						<tbody>
						<?php $rules->printTable(); ?>
						</tbody>
					
					
					</table>
				
				
				</div>
				
				
				<!--                    <p>-->
				<!--                        --><?php //$rules->printRules();?>
				<!---->
				<!--                    </p>-->
			
			
			</div>
		
		
		</div>
	
	</div>
	
	<script type = "text/javascript" src = "js/Offer/Rules/Geo.js"></script>
	<script type = "text/javascript" src = "js/Offer/Rules/Device.js"></script>
	
	<script type = "text/javascript">
		
		
		$("#searchCountryList").on('propertychange change keyup paste input', function () {
			searchCountryList($("#searchCountryList").val());
			
		});
		
		
		function searchCountryList(searchWords) {
			// Declare variables
			var filter, table, tr, td, i;
			
			filter = searchWords.toUpperCase();
			table = document.getElementById("countryListBody");
			tr = table.getElementsByTagName("tr");
			
			// Loop through all table rows, and hide those who don't match the search query
			for (i = 0; i < tr.length; i++) {
				td = tr[i].getElementsByTagName("td")[0];
				if (td) {
					if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
						tr[i].style.display = "";
					} else {
						tr[i].style.display = "none";
					}
				}
			}
		}
		
		function editRule(ruleID, ruleType) {
			switch (ruleType) {
				
				case "geo":
					$("#geoRuleID").val(ruleID);
					var geo = new geoEdit(ruleID);
					geo.loadGeoRule();
					$('#geoModal').modal('show');
					break;
				
				case "device":
					$("#deviceRuleID").val(ruleID);
					var device = new deviceEdit(ruleID);
					device.loadRule();
					$('#deviceModal').modal('show');
					
					break;
				
				
			}
		}
		
		
		$("#geoCreateButton").click(function () {

			$.ajax({
				type: "POST",
				url: "/scripts/offer/rules/geo/addGeo.php",
				data: {data: parseCountries("toAdd")},
				cache: false,
				success: function (result) {
					
					$("#geoModal").modal("hide");
					location.reload();
					
					
				}
				
			});
			
		});
		
		$("#deviceCreateButton").click(function () {
			$.ajax({
				type: "POST",
				url: "/scripts/offer/rules/device/add.php",
				data: {data: parseDevices("deviceToAdd")},
				cache: false,
				success: function (result) {
					
					$("#deviceModal").modal("hide");
					location.reload();
					
				}
				
				
			});
			
		});
		
		function resetDeviceModal() {
			
			var rows = $('#deviceToAdd > tbody > tr');
			
			$("#deviceRuleName").val("");
			$("#deviceRuleID").val("");
			$("#deviceRedirectOffer").val("");
			$("#deviceRuleTitle").text("New Device Rule");
			$("#deviceIsAllowed").attr("checked", false);
			$("#deviceIsActive").attr("checked", true);
			
			$("#deviceCancelButton").click(function () {
				resetDeviceModal()
			});
			
			$("#deviceCreateButton").show();
			$("#deviceUpdateButton").hide();
			
			
			for (var i = 0; i < rows.length; i++) {
				$("#deviceListBody").append(rows[i]);
				
				$("#_" + rows[i].id).attr("onclick", "addDevice(\"" + rows[i].id + "\")");
				
				$("#" + rows[i].id + "_img").attr("src", "images/icons/add.png");
			}
			
		}
		
		
		function resetGeoModal() {
			
			
			var rows = $('#toAdd > tbody > tr');
			
			$("#geoRuleName").val("");
			$("#geoRuleID").val("");
			$("#geoRedirectOffer").val("");
			$("#geoRuleTitle").text("New Geo Rule");
			$("#geoIsAllowed").attr("checked", false);
			$("#geoIsActive").attr("checked", true);
			
			$("#geoCancelButton").click(function () {
				resetGeoModal()
			});

			$("#geoModal").click(function() {
				resetGeoModal();
			});

			$("button.close").click(function() {
				resetGeoModal();
			});
			
			$("#geoCreateButton").show();
			$("#geoUpdateButton").hide();

			for (var i = 0; i < rows.length; i++) {
				rows[i].lastChild.remove();
				$("#countryListBody").append(rows[i]);
				
				$("#_" + rows[i].id).attr("onclick", "addCountry(\"" + rows[i].id + "\")");
				
				$("#" + rows[i].id + "_img").attr("src", "images/icons/add.png");
			}
			
			sortCountries("a", "asc");
			
			
		}
		
		$("#geoCancelButton").click(function () {
			resetGeoModal()
		});
		
		$("#deviceCancelButton").click(function () {
			resetDeviceModal()
		});
		
		
		function addDevice(deviceName) {
			var selectedDeviceTR = $("#" + deviceName);
			
			
			$("#deviceList tbody").remove(selectedDeviceTR);
			
			$("#deviceToAdd tbody").append(selectedDeviceTR);
			
			$("#_" + deviceName).attr("onclick", "removeDevice('" + deviceName + "');");
			
			$("#" + deviceName + "_img").attr("src", "images/icons/cancel.png");
			
			
		}
		
		
		function removeDevice(deviceName) {
			var selectedCountry = $("#" + deviceName);
			
			$(selectedCountry).remove();
			
			
			$("#deviceListBody").append("<tr id=\"" + deviceName + "\" >" + selectedCountry.html() + "</tr>");
			
			
			$("#_" + deviceName).attr("onclick", "addDevice(\"" + deviceName + "\")");
			
			$("#" + deviceName + "_img").attr("src", "images/icons/add.png");
		}
		
		function parseDevices(tableName, onlyCountries = false) {
			var rows = $('#' + tableName + ' > tbody > tr');
			
			var offerID = $("#offerID").val();
			
			var redirectOffer = $("#deviceRedirectOffer").val();
			
			var ruleName = $("#deviceRuleName").val();
			
			var notAllowed = document.getElementById("geoIsAllowed").checked;

			var capAmount = $("#deviceCap").val();
			var capStatus = $("#capIsActive").is(":checked");
			
			var parsed = [];
			if (!onlyCountries)
				parsed = [offerID, ruleName, redirectOffer, notAllowed, capAmount, capStatus];
			
			for (var i = 0; i < rows.length; i++)
				parsed.push(rows[i].id);
			
			return JSON.stringify(parsed);
			
		}
		
		
		function parseCountries(tableName, onlyCountries = false) {
			var rows = $('#' + tableName + ' > tbody > tr');
			
			
			var offerID = $("#offerID").val();
			
			var redirectOffer = $("#geoRedirectOffer").val();
			
			var geoRuleName = $("#geoRuleName").val();
			
			var countriesNotAllowed = document.getElementById("geoIsAllowed").checked;
			
			var parsed = [];
			if (!onlyCountries)
				parsed = [offerID, geoRuleName, redirectOffer, countriesNotAllowed];
			
			for (var i = 0; i < rows.length; i++) {
				parsed.push([
					rows[i].id, 
					rows[i].children[0].innerText,
					rows[i].children[2].firstChild.firstChild.checked || 0, 
					rows[i].children[2].lastChild.lastChild.value
				]);
			}

			return JSON.stringify(parsed);
		
		}
		
		function sortTable(table, order) {
			var asc = order === 'asc',
				tbody = table.find('tbody');
			
			tbody.find('tr').sort(function (a, b) {
				if (asc) {
					return $('td:first', a).text().localeCompare($('td:first', b).text());
				} else {
					return $('td:first', b).text().localeCompare($('td:first', a).text());
				}
			}).appendTo(tbody);
		}
		
		function sortCountries(table, order) {
			var asc = order === 'asc',
				tbody = $("#countryListBody");
			
			tbody.find('tr').sort(function (a, b) {
				if (asc) {
					return $('td:first', a).text().localeCompare($('td:first', b).text());
				} else {
					return $('td:first', b).text().localeCompare($('td:first', a).text());
				}
			}).appendTo(tbody);
		}
		
		
		function addCountry(countryName, capStatus = 0, cap = 0, sortTableAfter = true) {
			
			capIsActive = capStatus ? "checked" : "";

			var c = $("#" + countryName);
			
			$("#countryList tbody").remove(c);
		
			if(!document.getElementById(countryName + '_capIsActive')) {
				const html =
					'<td class="caps">' +
						'<span><input class="cap_active" id="' + countryName + '_capIsActive"' + capIsActive + ' type="checkbox" style = "width:15px;height:15px;">' +
							'<span>Enable Cap</span></span>' +
						'<span><label for = "geoCap">Cap:</label>' +
						'<input class="cap_amount" type = "number" id = "' + countryName + '_geoCap" value=' + cap + '></span>' +
					'</td>';
					c.append(html)
			}
			
			$("#toAdd tbody").append(c);

			$("#_" + countryName).attr("onclick", "removeCountry('" + countryName + "');");
			
			$("#" + countryName + "_img").attr("src", "images/icons/cancel.png");
			
			if (sortTableAfter)
				sortTable($('#toAdd'), 'asc');
			
			
		}
		
		function removeCountry(countryName, sortTableAfter = true) {
			var selectedCountry = $("#" + countryName);
			
			
			$(selectedCountry).remove();
			selectedCountry[0].lastChild.remove();
			
			$("#countryListBody").append("<tr id=\"" + countryName + "\" >" + selectedCountry.html() + "</tr>");
			
			$("#_" + countryName).attr("onclick", "addCountry('" + countryName + "');");

			$("#" + countryName + "_img").attr("src", "images/icons/add.png");
			
			if (sortTableAfter)
				sortCountries($('#countryList'), 'asc');
			
			
		}
		
		//        $('.modal-content').resizable({
		//            //alsoResize: ".modal-dialog",
		//            minHeight: 300,
		//            minWidth: 300
		//        });
		$('.modal-dialog').draggable();
		
		$('#geoModal').on('show.bs.modal', function () {
			$(this).find('.modal-body').css({
				'max-height': '100%'
			});
		});
	
	
	</script>
<?php include "footer.php"; ?>