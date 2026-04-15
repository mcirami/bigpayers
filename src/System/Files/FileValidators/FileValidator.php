<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/5/2018
 * Time: 12:13 PM
 */

namespace LeadMax\TrackYourStats\System\File\FileValidators;


abstract class FileValidator
{

    abstract function validateFile($file): bool;

    function validateFiles(array $files): bool
    {
        foreach ($files as $file) {
            if ($this->validateFile($file) == false) {
                return false;
            }
        }

        return true;
    }

}