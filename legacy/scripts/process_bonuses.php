<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/2/2018
 * Time: 11:44 AM
 */


$affiliates = \LeadMax\TrackYourStats\User\User::selectAllAffiliateIDs()->fetchAll(PDO::FETCH_OBJ);

foreach ($affiliates as $affiliateId)
{
	$bonus = new \LeadMax\TrackYourStats\User\Bonus($affiliateId->idrep);
	$bonus->processAll();
}

send_to('/bonus.php');

