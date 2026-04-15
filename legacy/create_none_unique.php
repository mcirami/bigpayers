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

if (isset($_POST["name"]))
{
	$rule                 = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\NoneUnique();
	$rule->name           = $_POST["name"];
	$rule->redirect_offer = $_POST["redirect_offer"];
	$rule->offer_idoffer  = $_GET["id"];
	$rule->is_active      = $_POST["is_active"];
	$rule->save();
	
	send_to("offer_edit_rules.php?offid={$_GET["id"]}");
}

$offer_view = new  \LeadMax\TrackYourStats\Offer\View(\LeadMax\TrackYourStats\System\Session::userType());
$offerList  = $offer_view->getUsersQuery()->fetchAll(PDO::FETCH_OBJ);

$offer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($_GET["id"])->fetch(PDO::FETCH_OBJ);


?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table ">
		<div class = "heading_holder">
			<span class = "lft value_span9">Create None Unique Rule for Offer "<?= $offer->offer_name ?>"</span>
		
		</div>
		
		<div class = "white_box_x_scroll white_box  value_span8 ">
			<div class = "left_con01">
				
				
				<form action = "create_none_unique.php?id=<?= $_GET["id"] ?>" method = "post">
					<p>
						<label for = "name">Name:</label>
						<input type = "text" name = "name" value = "">
					</p>
					
					
					<p>
						<label for = "name">Redirect Offer:</label>
						<select name = "redirect_offer">
							<?php
							foreach ($offerList as $offer2)
							{
								if ($offer2->idoffer !== $offer->idoffer)
								{
									echo "<option value=\"{$offer2->idoffer}\">{$offer2->offer_name}</option>";
								}
								
							}
							?>
						</select>
					</p>
					
					
					<p>
						<label for = "is_active">Status:</label>
						<select name = "is_active">
							<?php
							
							
							echo "<option selected value=\"1\"><span color='green'>Active</span></option>";
							echo "<option value=\"0\"><span color='red'>In-Active</span></option>";
							
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
