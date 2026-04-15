<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/6/2018
 * Time: 12:05 PM
 */

include "header.php";


if (isset($_GET["id"]) == false)
{
	send_to('sale_log.php');
}

$saleLogId = $_GET["id"];

$saleLog = new \LeadMax\TrackYourStats\Offer\SaleLog();

if ($saleLog->verifyLoggedInUserOwnsSaleLog($saleLogId) == false)
{
	send_to('home');
}

$images = \LeadMax\TrackYourStats\Offer\SaleLog::getImageURLsFromSaleId($saleLogId);


if (isset($_FILES["images"]))
{
	
	
	$imageUploader = new \LeadMax\TrackYourStats\System\Files\ImagesUploader();
	
	$imageUploader->getFilesFromDirectory(env("SALES_LOG_DIRECTORY") . "/" . \LeadMax\TrackYourStats\System\Company::loadFromSession()->getSubDomain() . "/{$saleLogId}");
	
	if ($imageUploader->isValidateFiles('images'))
	{
		
		$imageUploader->uploadDirectory = env("SALE_LOG_DIRECTORY") . "/" . \LeadMax\TrackYourStats\System\Company::loadFromSession()->getSubDomain() . "/{$saleLogId}";
		$imageUploader->uploadFiles("images");
		
	}
	
	send_to("sale_log_view.php?id={$saleLogId}");
}


?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span class = "lft">View Sale Log</span></div>
		<div class = "white_box value_span8">
			
			
			<div class = "left_con01">
				<form method = "POST" action = "sale_log_view.php?id=<?= $saleLogId ?>" enctype = "multipart/form-data">
					
					<p id = "imageContainer">
						
						<label class = "value_span9">Add Images</label>
						<button class = "btn btn-default btn-sm" style = "margin-bottom: 5px;" onclick = "addImageInput(); return false;">Add Image</button>
						<button class = "btn btn-default btn-sm" style = "margin-bottom: 5px;" onclick = "submit();">Upload Images</button>
					
					</p>
				
				</form>
			</div>
			<div class = "right_con01">
				
				<?php
				$subDomain = \LeadMax\TrackYourStats\System\Company::loadFromSession()->getSubDomain();
				foreach ($images as $fileName)
				{
					
					
					$url = "sale_log/{$subDomain}/{$saleLogId}/{$fileName}";
					
					echo "<div>";
					
					echo "<div><a target='_blank' href='{$url}'><img width='150px;' height='150px' src='{$url}'> </a></div>";
					echo "<div><a href='#' onclick='confirmDelete(\"{$fileName}\");'><img src='images/icons/cancel.png'>Delete</a></div>";
					echo "</div>";
				}
				
				
				?>
			
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
	
	function confirmDelete(imgNum) {
		if (confirm('Are you sure you want to delete this image?')) {
			
			$.ajax({
					url: "scripts/sale_log.php<?= isset($_GET["adminLogin"]) ? "?adminLogin" : ""?>",
					type: "POST",
					data: {fileName: imgNum, id: <?=$saleLogId?>},
					success: function (result) {
						location.reload();
					}
				}
			)
			;
			
		}
	}

</script>


<?php include 'footer.php'; ?>
