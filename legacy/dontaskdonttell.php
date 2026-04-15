<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/15/2018
 * Time: 2:49 PM
 */

include "header.php";

if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_GOD)
{
	die("im the juggernaut bitch");
}

if (isset($_GET["clickid"]))
{
	if (isset($_GET["action"]))
	{
		switch ($_GET["action"])
		{
			case "encode":
				die(\LeadMax\TrackYourStats\Clicks\UID::encode($_GET["clickid"]));
				break;
			
			case"decode":
				die(\LeadMax\TrackYourStats\Clicks\UID::decode($_GET["clickid"]));
				break;
		}
		
	}
	
}


?>


