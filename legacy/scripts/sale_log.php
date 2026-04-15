<?php

if (isset($_POST["id"]) && isset($_POST["fileName"]))
{
	$saleLog     = new \LeadMax\TrackYourStats\Offer\SaleLog();
	$saleLog->id = $_POST["id"];
	
	if ($saleLog->verifyLoggedInUserOwnsSaleLog($_POST["id"]))
	{
		if ($saleLog->renameSaleImage($_POST["fileName"]))
		{
			die('true');
		}
		else
		{
			die("false");
		}
	}
	
	
}