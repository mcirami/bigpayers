<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/9/2018
 * Time: 1:57 PM
 */

include 'header.php';


if (isset($_GET["id"]) == false)
{
	send_to('sale_log.php');
}

$saleLogId = $_GET["id"];

$saleLog = \Offer\SaleLog::selectOneQuery($saleLogId)->fetch(PDO::FETCH_OBJ);

if ($saleLog == false)
{
	send_to('home.php');
}

if (\LeadMax\TrackYourStats\Clicks\Conversion::doesUserOwnConversion(\LeadMax\TrackYourStats\System\Session::userID(), $saleLog->conversion_id) == false)
{
	send_to('home.php');
}

$images = \Offer\SaleLog::getImageURLsFromSaleId($saleLogId);

?>


<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span class = "lft">View Sale Log</span></div>
		<div class = "white_box value_span8">
			
			
				<?php
				foreach ($images as $fileName)
				{
					$url = "resources/sale_log/{$saleLogId}/{$fileName}";
					echo "<a target='_blank' href='{$url}'><img width='250px;' height='250px' src='{$url}'> </a>";
				}
				
				
				?>
		</div>
	
	</div>
</div>


<?php include 'footer.php'; ?>
