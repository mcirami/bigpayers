<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/30/2017
 * Time: 1:34 PM
 */


ini_set('display_errors', 1);

ini_set('memory_limit', '256M');

$updater = new \LeadMax\TrackYourStats\System\GeoIPUpdater();
$path = $updater->downloadLatest();
echo "Downloaded latest..<br/>";
$path = $updater->decompressAndUnZip($path);
echo "Decompressed..<br/>";
echo "Testing DB...<br/>";
if($updater->testGeoDB($path))
{
    echo "DB good! Updating.. <br/>";
    if($updater->updateWithLatest($path))
        echo "Success!";
    else
        echo "failed to update with latest..";
}

else
    echo "Bad DB..";
