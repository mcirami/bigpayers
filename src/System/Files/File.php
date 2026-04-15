<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/5/2018
 * Time: 12:51 PM
 */

namespace LeadMax\TrackYourStats\System\Files;


use LeadMax\TrackYourStats\System\File\FileValidators\FileValidator;

abstract class File
{


    public $manyFiles = false;

    private $postName;

    private $validator;

    public function __construct($postName, FileValidator $validator = null)
    {
        $this->postName = $postName;

        if ($validator) {
            $this->validator = $validator;
        }
    }


    public function validate()
    {
        if ($this->manyFiles) {
            $this->validator->validateFiles();
        }
    }

}