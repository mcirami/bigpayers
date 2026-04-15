<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/8/2018
 * Time: 12:06 PM
 */

include "header.php";


if (isset($_GET["sid"]) == false)
{
	send_to('home.php');
}


$saleId = $_GET["sid"];


$saleLog = \Offer\SaleLog::selectOneQuery($saleId);

if ($conversion->user_id !== \LeadMax\TrackYourStats\System\Session::userID())
{
	send_to('home.php');
}

if (isset($_POST["button"]))
{

}

$click = \Clicks\Click::SelectOne($conversion->click_id);

$offer = \LeadMax\TrackYourStats\Offer\Offer::selectOneQuery($click->offer_idoffer)->fetch(PDO::FETCH_OBJ);

?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span class = "lft">Log Sale for '<?= $offer->offer_name ?>'</span></div>
		<div class = "white_box value_span8">
			
			<form action = "log_sale.php?cid=<?= $conversion_id ?>" method = "post" id = "form"
				  enctype = "multipart/form-data">
				
				
				<div class = "left_con01">
					<?php
					if (isset($error))
					{
						echo "<span class='small_text value_span10'>{$error}</span>";
					}
					
					?>
					
					<p>
						<label class = "value_span9">Sale Timestamp</label>
						<input type = "text" value = "<?= $conversion->timestamp ?>" disabled>
					</p>
					
					
					<p id = "imageContainer">
						
						<label class = "value_span9">Add Images</label>
						<button class = "btn btn-default btn-sm" onclick = "addImageInput(); return false;">Add Image</button>
					<div class = "input-group ">
						<input class = "form-control " type = "file" name = "images[]" accept = "image/*"><br>
					</div>
					
					</p>
				
				</div>
				
				
				<div class = "right_con01">
					<span class = "btn_yellow"> <input type = "submit" name = "button" class = "value_span6-2 value_span2 value_span1-2" value = "Log Sale" onclick = ""/></span>
				</div>
		</div>
	
	</div>
</div>


<script type = "text/javascript">
	var counter = 1;
	
	function addImageInput() {
		counter++;
		if (counter >= 15)
			alert('yarly');
		$("#imageContainer").append("\t<div class = \"input-group \" id=\"img_" + counter + "\">\n" +
			"\t\t\t\t\t\t\t<input class = \"form-control \" type = \"file\" name = \"images[]\" accept = \"image/*\"><br>\n" +
			"\t\t\t\t\t\t\t<span class = \"input-group-btn\">\n" +
			"\t\t\t\t\t\t\t\t<a href = \"#\" class = \"btn btn-sm btn-danger\" onclick='removeImageInput(" + counter + ");'>X</a>\n" +
			"\t\t\t\t\t\t</span>\n" +
			"\t\t\t\t\t\t</div>");
	}
	
	function removeImageInput(num) {
		$("#img_" + num).remove();
	}

</script>

<?php include 'footer.php'; ?>

