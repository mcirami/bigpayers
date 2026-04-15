<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/20/2018
 * Time: 4:38 PM
 */

include "header.php";


if (\LeadMax\TrackYourStats\System\Session::permissions()->can(\LeadMax\TrackYourStats\User\Permissions::EDIT_OFFER_RULES) == false)
{
	send_to('home.php');
}

if (isset($_GET["id"]) == false)
{
	send_to('home.php');
}

$rule = \LeadMax\TrackYourStats\Offer\Rules\Handlers\NoneUnique::loadFromId($_GET["id"]);

if (isset($_POST["name"]))
{
	$rule->name           = $_POST["name"];
	$rule->redirect_offer = $_POST["redirect_offer"];
	$rule->is_active      = $_POST["is_active"];
	$rule->update();
	
	send_to("offer_edit_rules.php?offid={$rule->offer_idoffer}");
}

$offer_view = new \LeadMax\TrackYourStats\Offer\View(\LeadMax\TrackYourStats\System\Session::userType());
$offerList  = $offer_view->getUsersQuery()->fetchAll(PDO::FETCH_OBJ);


?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table ">
		<div class = "heading_holder">
			<span class = "lft value_span9">Edit None Unique Rule</span>
		
		</div>
		
		<div class = "white_box_x_scroll white_box  value_span8 ">
			<div class = "left_con01">
				
				
				<form action = "edit_none_unique.php?id=<?= $_GET["id"] ?>" method = "post">
					<p>
						<label for = "name">Name:</label>
						<input type = "text" name = "name" value = "<?= $rule->name ?>">
					</p>
					
					
					<p>
						<label for = "name">Redirect Offer:</label>
						<select name = "redirect_offer">
							<?php
							foreach ($offerList as $offer)
							{
								if ($offer->idoffer !== $rule->offer_idoffer)
								{
									if ($offer->idoffer == $rule->redirect_offer)
									{
										echo "<option selected value=\"{$offer->idoffer}\">{$offer->offer_name}</option>";
									}
									else
									{
										echo "<option value=\"{$offer->idoffer}\">{$offer->offer_name}</option>";
									}
								}
								
							}
							?>
						</select>
					</p>
					
					
					<p>
						<label for = "is_active">Status:</label>
						<select name = "is_active">
							<?php
							
							$active   = $rule->is_active == 1 ? "selected" : "";
							$inActive = $rule->is_active == 1 ? "" : "selected";
							
							echo "<option
							{
							$active
							} value=\"1\"><span color='green'>Active</span></option>";
							echo "<option
							{
							$inActive
							} value=\"0\"><span color='red'>In-Active</span></option>";
							
							?>
						
						</select>
					</p>
					
					
					<input class = "btn btn-default btn-success" type = "submit" value = "Save" name = "submit">
				
				</form>
			</div>
		</div>
	</div>
	<!--right_panel-->
	
	
	<?php include 'footer.php'; ?>
