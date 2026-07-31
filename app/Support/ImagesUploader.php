<?php

namespace App\Support;

use App\Services\SaleLogImageStorage;

class ImagesUploader
{
    public $uploadDirectory;

    public $maxUploadSize = 16777216;

    private $allowedExtensions = ['png', 'jpg', 'jpeg'];

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
        $directory = SaleLogImageStorage::companyDirectory();

        return file_exists($directory) || mkdir($directory);
    }

    public function uploadFile($postName)
    {
    }

    public function uploadFiles($postName)
    {
        if (! $this->isValidateFiles($postName)) {
            return false;
        }

        $this->doesCompanySubDomainFolderExist();

        if (! $this->doesDirectoryExist($this->uploadDirectory)) {
            $this->makeDirectory($this->uploadDirectory);
            $fileNumberStart = 0;
        } else {
            $fileNumberStart = $this->getDirectoryFileCount($this->uploadDirectory);
        }

        $numberOfFiles = count($_FILES[$postName]['name']);
        $filesInDirectory = $this->getFilesFromDirectory($this->uploadDirectory);

        for ($index = 0; $index < $numberOfFiles; $index++) {
            $extension = pathinfo($_FILES[$postName]['name'][$index], PATHINFO_EXTENSION);
            $fileName = in_array($fileNumberStart.$extension, $filesInDirectory)
                ? uniqid().'.'.$extension
                : $fileNumberStart.'.'.$extension;

            move_uploaded_file(
                $_FILES[$postName]['tmp_name'][$index],
                $this->uploadDirectory.'/'.$fileName
            );
            $fileNumberStart++;
        }

        return true;
    }

    public function getFilesFromDirectory($path)
    {
        if (! file_exists($path)) {
            return false;
        }

        $files = scandir($path);
        unset($files[0], $files[1]);

        return $files;
    }

    public function getDirectoryFileCount($path)
    {
        return count(scandir($path)) - 2;
    }

    public function isValidateFiles($postName)
    {
        return isset($_FILES[$postName])
            && ! $this->doesFilesHaveErrors($postName)
            && ! $this->isFilesOverMaxSize($postName)
            && $this->isValidExtensions($postName);
    }

    private function isValidExtensions($postName)
    {
        foreach ($_FILES[$postName]['name'] as $fileName) {
            if (! in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->allowedExtensions)) {
                return false;
            }
        }

        return true;
    }

    private function doesFilesHaveErrors($postName)
    {
        foreach ($_FILES[$postName]['error'] as $error) {
            if ($error !== 0) {
                return true;
            }
        }

        return false;
    }

    private function isFilesOverMaxSize($postName)
    {
        foreach ($_FILES[$postName]['size'] as $size) {
            if ($size >= $this->maxUploadSize) {
                return true;
            }
        }

        return false;
    }
}
