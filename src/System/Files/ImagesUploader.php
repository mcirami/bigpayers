<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/5/2018
 * Time: 12:07 PM
 */

namespace LeadMax\TrackYourStats\System\Files;


use LeadMax\TrackYourStats\System\Company;

class ImagesUploader
{


    public $uploadDirectory;

    public $maxUploadSize = 16777216;

    private $allowedExtensions = [
        'png',
        'jpg',
        'jpeg',
    ];

    public function __construct()
    {
    }

    public function doesDirectoryExist($path)
    {
        return file_exists($path);
    }

    public function makeDirectory($path)
    {
        return mkdir($path);
    }

    public function doesCompanySubDomainFolderExist()
    {
        $dir = env('SALE_LOG_DIRECTORY').'/'.Company::loadFromSession()->getSubDomain();
        if (file_exists($dir) == false) {
            return mkdir($dir);
        } else {
            return true;
        }
    }

    public function uploadFile($postName)
    {

    }

    public function uploadFiles($postName)
    {
        if ($this->isValidateFiles($postName) == false) {
            return false;
        }

        $this->doesCompanySubDomainFolderExist();

        if ($this->doesDirectoryExist($this->uploadDirectory) == false) {
            $this->makeDirectory($this->uploadDirectory);
            $fileNumberStart = 0;
        } else {
            $fileNumberStart = $this->getDirectoryFileCount($this->uploadDirectory);
        }

        $numberOfFiles = count($_FILES[$postName]["name"]);

        $filesInDirectory = $this->getFilesFromDirectory($this->uploadDirectory);

        for ($i = 0; $i < $numberOfFiles; $i++) {
            $extension = pathinfo($_FILES[$postName]["name"][$i], PATHINFO_EXTENSION);

            if (in_array($fileNumberStart.$extension, $filesInDirectory)) {
                $fileName = uniqid().".".$extension;
            } else {
                $fileName = "$fileNumberStart.$extension";
            }


            move_uploaded_file($_FILES[$postName]["tmp_name"][$i], $this->uploadDirectory."/{$fileName}");

            $fileNumberStart++;
        }

        return true;
    }

    public function getFilesFromDirectory($path)
    {
        if (file_exists($path)) {
            $files = scandir($path);
            unset($files[0]);
            unset($files[1]);
        } else {
            return false;
        }


        return $files;
    }

    public function getDirectoryFileCount($path)
    {
        $files = scandir($path);

        return count($files) - 2;
    }

    private function cleanTempFiles($postName)
    {

    }

    public function isValidateFiles($postName)
    {
        if (isset($_FILES[$postName]) == false) {
            return false;
        }


        if ($this->doesFilesHaveErrors($postName)) {
            return false;
        }

        if ($this->isFilesOverMaxSize($postName)) {
            return false;
        }

        if ($this->isValidExtensions($postName) == false) {
            return false;
        }

        return true;
    }


    private function isValidExtensions($postName)
    {
        foreach ($_FILES[$postName]["name"] as $fileName) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if (in_array($ext, $this->allowedExtensions) == false) {
                return false;
            }
        }

        return true;
    }

    private
    function doesFilesHaveErrors(
        $postName
    ) {
        foreach ($_FILES[$postName]["error"] as $error) {
            if ($error !== 0) {
                return true;
            }
        }

        return false;
    }

    private
    function isFilesOverMaxSize(
        $postName
    ) {
        foreach ($_FILES[$postName]["size"] as $size) {
            if ($size >= $this->maxUploadSize) {
                return true;
            }
        }

        return false;
    }

}