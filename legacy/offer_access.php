<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/22/2018
 * Time: 11:31 AM
 */
include 'header.php';


$assignments = new \LeadMax\TrackYourStats\Table\Assignments(['!id' => 0]);
$assignments->getAssignments();
$assignments->setGlobals();

$id = (int) $id;

/*if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_offers") == false)
{
	send_to('home.php');
}*/


if (\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($id, \LeadMax\TrackYourStats\System\Session::userID()) == false)
{
	send_to('home.php');
}

$offer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($id)->fetch(PDO::FETCH_OBJ);
if ($offer->parent != null)
{
	send_to('home.php');
}


$users = \LeadMax\TrackYourStats\User\User::selectAllOwnedAffiliates()->fetchAll(PDO::FETCH_ASSOC);

$assignedAffiliates = \LeadMax\TrackYourStats\Offer\RepHasOffer::queryGetAffiliatesAssignedToOffer($id)->fetchAll(PDO::FETCH_OBJ);
$affiliateIds       = [];

foreach ($assignedAffiliates as $affiliate)
	$affiliateIds[] = $affiliate->idrep;

foreach ($users as &$user)
{
	if (in_array($user["idrep"], $affiliateIds))
	{
		$user["has_offer"] = 1;
	}
	else
	{
		$user["has_offer"] = 0;
	}
}


if (isset($_POST["userList"]))
{
	\LeadMax\TrackYourStats\Offer\RepHasOffer::massAssignAffiliates($_POST["userList"], [$id]);
	$updateOccurred = true;
}

if (isset($_POST["unAssign"]))
{
	\LeadMax\TrackYourStats\Offer\RepHasOffer::unAssignAffiliates($_POST["unAssign"], $id);
	$updateOccurred = true;
}

if (isset($updateOccurred))
{
	send_to("offer_access.php?id={$id}");
}

?>
<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span class = "lft">Offer Access - <?= $offer->offer_name ?></span></div>
		<div class = "white_box value_span8">
			
			<form action = "offer_access.php?id=<?= $id ?>" method = "post" id = "form"
				  enctype = "multipart/form-data">
				
				
				<div class = "left_con01" id = "users">
					<a class = "btn btn-default btn-sm" href = "javascript:void(0);" onclick = "checkAll()">Check
																											All</a>
					
					<a class = "btn btn-default btn-sm" href = "javascript:void(0);" onclick = "uncheckAll()">UnCheck
																											  All</a>
					<p>
						<?php
						foreach ($users as $user)
						{
							$checked = ($user["has_offer"] == 1) ? "checked" : "";
							if ($user["has_offer"] == 0)
							{
								echo "<input type='hidden' value='{$user["idrep"]}' id='user_{$user["idrep"]}' name='unAssign[]'>";
							}
							echo "<label><input {$checked} class='fixCheckBox' type='checkbox' name='userList[]' value='{$user["idrep"]}'> {$user["user_name"]} </label>";
						}
						?>
					</p>
				</div>
				
				<div class = "right_con01" id = "offers">
					
					<span class = "btn_yellow"> <input type = "submit" name = "button" class = "value_span6-2 value_span2 value_span1-2"
													   value = "Save" onclick = ""/></span>
				
				</div>
		</div>
	
	
	</div>
</div>


<!--right_panel-->

<script type = "text/javascript">
	
	
	function checkAll() {
		$("input[type='checkbox']").each(function () {
			if (this.checked === false) {
				removeFromUnAssign(this);
				this.click();
			}
			
		});
	}
	
	
	function uncheckAll() {
		$("input[type='checkbox']").each(function () {
			
			if (this.checked) {
				
				addToUnAssign(this);
				this.click();
			}
			
		});
	}
	
	
	function addToUnAssign(element) {
		var input = $("<input type=\"hidden\" name=\"unAssign[]\" id=\"user_" + element.value + "\"" + " value=\"" + element.value + "\">");
		console.log(input);
		$("#users").append(input);
	}
	
	function removeFromUnAssign(element) {
		$("#user_" + element.value).remove();
	}
	
	$("input[type='checkbox']").click(function () {
		if ($(this).is(":checked") === false) {
			addToUnAssign(this);
		}
		else {
			removeFromUnAssign(this);
		}
	});


</script>


<?php include 'footer.php'; ?>


