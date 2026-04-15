<?php namespace LeadMax\TrackYourStats\System;

use GeoIp2\Record\MaxMind;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/10/2017
 * Time: 2:41 PM
 */

use GeoIp2\Database\Reader;

class GeoIPUpdater
{


    protected $licenseKey = "MmAtpnCsjU89";

    public $defaultURL = "https://download.maxmind.com/app/geoip_download?edition_id=GeoIP2-City&suffix=tar.gz&license_key=";

    public $downloadURL = "";

    private $rootPath = __DIR__;

    private $folderName = "";

    public function __construct($licenseKey = false)
    {
        if ($licenseKey) {
            $this->downloadURL = $this->defaultURL.$licenseKey;
        } else {
            $this->downloadURL = $this->defaultURL.$this->licenseKey;
        }

        $this->rootPath = env("GEO_IP_DATABASE");
    }

    public function testGeoDB($filePath)
    {
        try {
            $reader = new Reader($filePath);

            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function updateWithLatest($filePath)
    {
        try {

            rename($filePath, $this->rootPath."/resources/geoip.tmp");

            if (file_exists($this->rootPath."/resources/GeoIP2-City.mmdb.bak")) {
                $oldHash = hash_file("sha256", $this->rootPath."/resources/GeoIP2-City.mmdb");
                $newHash = hash_file("sha256", $this->rootPath."/resources/geoip.tmp");
                if ($oldHash != $newHash) {
                    rename($this->rootPath."/resources/GeoIP2-City.mmdb",
                        $this->rootPath."/resources/GeoIP2-City.mmdb.bak");
                } else {
                    echo "Original file is latest, not moving to .bak ...<br/>";
                }
            } else {
                rename($this->rootPath."/resources/GeoIP2-City.mmdb",
                    $this->rootPath."/resources/GeoIP2-City.mmdb.bak");
            }

            rename($this->rootPath."/resources/geoip.tmp", $this->rootPath."/resources/GeoIP2-City.mmdb");
            $this->cleanUp();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function cleanUp()
    {
        $geoipPath = $this->rootPath."/resources/geoip/";
        if (file_exists($geoipPath."geo_db_temp.tar")) {
            unlink($geoipPath."geo_db_temp.tar");
        }


        if (file_exists($geoipPath."geo_db_temp.tar.gz")) {
            unlink($geoipPath."geo_db_temp.tar.gz");
        }

        if (file_exists($geoipPath.$this->folderName)) {
            rmdir($geoipPath.$this->folderName);
        }

        echo "Cleaned up files.. <br/>";

        return true;

    }

    public function decompressAndUnZip($filePath)
    {
        $p = new \PharData($filePath);

        $filePath = str_replace(".gz", "", $filePath);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $p->decompress();


        $phar = new \PharData($filePath);
        foreach ($phar as $file) {
            $this->folderName = basename($file);
            break;
        }


        $phar->extractTo($this->rootPath."/resources/geoip/", $this->folderName."/GeoIP2-City.mmdb", true);


        return $this->rootPath."/resources/geoip/".$this->folderName."/GeoIP2-City.mmdb";
    }


    public function downloadLatest()
    {


        set_time_limit(0);
//This is the file where we save the    information
        $filePath = $this->rootPath.'/resources/geoip/geo_db_temp.tar.gz';
        $fp = fopen($filePath, 'w+');
//Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ", "%20", $this->downloadURL));
        curl_setopt($ch, CURLOPT_TIMEOUT, 150);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// write curl response to file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// get curl response
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        echo "Download complete <br/>";

        return $filePath;

    }


}