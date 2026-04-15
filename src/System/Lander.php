<?php

namespace LeadMax\TrackYourStats\System;

class Lander
{

    public $company;

    public $landerFile = "";
    public $customFileLoaded = false;

    public function __construct(Company $company)
    {

        $this->company = $company;

        if ($this->customFileCheck() == false) {
            $this->landerFile = env('TYS_LANDERS_DIRECTORY')."/".$this->company->getSubDomain()."/index.php";
           /* $this->landerFile = env('TYS_LANDERS_DIRECTORY')."/"."liontracking"."/index.php";*/

        }
    }

    private function customFileCheck()
    {
        if (isset($_GET["section"]) && strpos($_GET["section"], '..') === false) {
            $file = env('TYS_LANDERS_DIRECTORY')."/".$this->company->getSubDomain()."/".$_GET["section"].".php";
            if (file_exists($file)) {
                $this->landerFile = $file;
                $this->customFileLoaded = true;
            }
        }

        return $this->customFileLoaded;
    }

    public function loadCompanyLander()
    {

        if (!$this->isLandingPage()) {
            send_to("login.php");
        }

        if (file_exists($this->landerFile)) {
            if ($this->customFileLoaded) {
                include($this->landerFile);
                die();
            } else {
                $fileContents = file_get_contents($this->landerFile);
                $processedVars = $this->processCompanyVars($fileContents);
                echo $processedVars;
                die();
            }

        } else {
            send_to("login.php");
        }
    }

    public function isLandingPage()
    {
        return ($_SERVER["HTTP_HOST"] == $this->company->getLandingPage() && $this->company->getLandingPage());
        /*return ("liontracking" == $this->company->getLandingPage() && $this->company->getLandingPage());*/
    }

    private function processCompanyVars($fileContents)
    {
        $fileContents = str_replace("{email}", $this->company->getEmail(), $fileContents);

        $fileContents = str_replace("{skype}", $this->company->getSkype(), $fileContents);

        $fileContents = str_replace("{login_url}", $this->company->getLoginURL(), $fileContents);

        $fileContents = str_replace("{landing_page}", $this->company->getLandingPage(), $fileContents);

        return $fileContents;
    }

}