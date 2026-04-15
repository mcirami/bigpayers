<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/5/2018
 * Time: 12:13 PM
 */


namespace LeadMax\TrackYourStats\System\File\FileValidators;

class ImageValidator extends FileValidator
{

    public function validateFile($file): bool
    {
        if (getimagesize($file) === 0) {
            return false;
        }

        return true;
    }

    public static function cleanImage($file, $target)
    {
        $sourceImage = @imagecreatefromstring(@file_get_contents($file));

        if ($sourceImage === false) {
            return false;
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $targetImage = imagecreatetruecolor($width, $height);
        imagecopy($targetImage, $sourceImage, 0, 0, 0, 0, $width, $height);
        imagedestroy($sourceImage);
        imagepng($targetImage, $target);
        imagedestroy($targetImage);


    }

}