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
            $this->landerFile = $this->resolveIndexFile();
        }
    }

    private function customFileCheck()
    {
        if (isset($_GET["section"]) && strpos($_GET["section"], '..') === false) {
            $file = $this->resolveSectionFile($_GET["section"]);
            if ($file && file_exists($file)) {
                $this->landerFile = $file;
                $this->customFileLoaded = true;
            }
        }

        return $this->customFileLoaded;
    }

    public function loadCompanyLander()
    {
        if (!$this->isLandingPage()) {
            send_to("login");
        }

        if (file_exists($this->landerFile)) {
            if ($this->shouldIncludePhpFile($this->landerFile)) {
                include($this->landerFile);
                die();
            } else {
                $fileContents = file_get_contents($this->landerFile);
                $processedVars = $this->processCompanyVars($fileContents);
                echo $processedVars;
                die();
            }

        } else {
            send_to("login");
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

    public static function rootDirectory()
    {
        return storage_path('landers');
    }

    public static function companyDirectory($subDomain)
    {
        return rtrim(static::rootDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subDomain;
    }

    public static function resolveAssetPath($subDomain, $asset)
    {
        $companyDirectory = realpath(static::companyDirectory($subDomain));

        if ($companyDirectory === false) {
            return false;
        }

        $cleanAsset = ltrim(str_replace(['..\\', '../', "\0"], '', (string) $asset), '/\\');
        $candidate = $companyDirectory . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanAsset);
        $resolved = realpath($candidate);

        $allowedPrefix = $companyDirectory . DIRECTORY_SEPARATOR;

        if ($resolved === false || (strncmp($resolved, $allowedPrefix, strlen($allowedPrefix)) !== 0 && $resolved !== $companyDirectory)) {
            return false;
        }

        return $resolved;
    }

    private function resolveIndexFile()
    {
        $directory = static::companyDirectory($this->company->getSubDomain());

        foreach (['index.php', 'index.html'] as $indexFile) {
            $candidate = $directory . DIRECTORY_SEPARATOR . $indexFile;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return $directory . DIRECTORY_SEPARATOR . 'index.php';
    }

    private function resolveSectionFile($section)
    {
        $cleanSection = preg_replace('/[^A-Za-z0-9_\-\/]/', '', (string) $section);
        if ($cleanSection === '') {
            return false;
        }

        foreach (['.php', '.html'] as $extension) {
            $candidate = static::companyDirectory($this->company->getSubDomain()) . DIRECTORY_SEPARATOR . $cleanSection . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return false;
    }

    private function shouldIncludePhpFile($filePath)
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'php';
    }

}
