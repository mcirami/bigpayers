<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/31/2017
 * Time: 3:59 PM
 */

namespace LeadMax\TrackYourStats\System;

// SECURE FORM SUBMISSION
/*
A common problem in submitting forms to be inserted into the database, is that users can insert their own html into your form that you don't want to be there
e.g. adding a input with name of active_subscriber, or can_create_admins. When they shouldn't be accessing them at all

The solution? Random naming! Users will of course still be able to see the html, but it'll have random names.

This is how is works, first you pass an Associated Array into the constructor of SFS, inside that array will be all the names of the elements that are in your form.
Then, SFS randomly generates a string to assign to each element in the array, e.g. "userName" => "DH!@#UI", then, it implodes it into one string, and
submits it to the database with a randomly generated token.

Now, to use it in your HTML, you would go as such SFS->userName and it would return "DH!@#UI".

Additionally, you have to make sure the token is in your form, write SFS->printToken(); anywhere inside your form, and it'll print an invisible input with
the token value named as "sfs_token".

Now, to use it when actually submitting to the db, you would create a new SFS object, passing the token as a string to the constructor,

Ex: $sfs = new SFS($_POST["sfs_token"]);

SFS will know you passed a token, then, to get all of your element names, call the "pullElements()"

then, to access your POST names use it as such:


$_POST[$sfs->userName] (using PHP's __get magic function)


And that's how it works, this was a quick write up of the idea/concept, but should work good.

*/
class SFS
{

    public $elements = array();

    public $token = -1;

    function __construct($arg = false)
    {
        if (is_array($arg)) {
            $this->elements = $arg;
        } elseif (is_string($arg)) {
            $this->token = $arg;
        }


    }

    public function saveToSession()
    {
        $_SESSION["sfs"] = $this->elements;
    }


    public function loadFromSession()
    {
        $this->elements = $_SESSION["sfs"];
    }


    public function randomizeNames()
    {
        $newElements = array();
        foreach ($this->elements as $key => $val) {
            if (is_int($key)) {
                $newElements[$val] = $this->randomStr();
            } elseif (is_string($key)) {
                //found an array
                if ($this->isArray($key)) {

                    $newArrayName = $this->randomStr()."[]";

                    $newSubArray = array();

                    foreach ($val as $ele) {
                        $newSubArray[$ele] = $this->randomStr();
                    }

                    $newElements[$key] = $newArrayName.json_encode($newSubArray);


                } else {
                    $newElements[$key] = $this->randomStr();
                }

            }

        }

        $this->elements = $newElements;

    }

    public function get($name)
    {
        if (strpos($name, "[")) {

            $startBracket = strpos($name, "[");
            $endBracket = strpos($name, "]");

            // if they just want the array name
            if ($endBracket - $startBracket == 1) {
                $explode = explode("[]", $this->elements[$name]);

                return $explode[0]."[]";
            } else {
                $accessingElement = substr($name, $startBracket + 1);
                $accessingElement = substr($accessingElement, 0, strlen($accessingElement) - 1);


                $clean = substr($name, 0, strpos($name, "["));

                $parseJSON = $this->elements[$clean."[]"];

                $parseJSON = substr($parseJSON, strpos($parseJSON, "{"));

                $parsedArray = json_decode($parseJSON, true);

                return $parsedArray[$accessingElement];


            }


        } else {
            return $this->elements[$name];
        }
    }


    private function jsonToArray($str)
    {
        return explode(":", $str);
    }

    function __get($name)
    {

        return $this->elements[$name];
    }


    private function isArray($name)
    {
        if (strpos($name, "[]")) {
            return true;
        }

        return false;
    }

    private function randomStr()
    {
        return $this->salt(rand(5, 17));
    }

    private function salt($max = 40)
    {
        $i = 0;
        $salt = "";
        $characterList = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        while ($i < $max) {
            $salt .= $characterList{mt_rand(0, (strlen($characterList) - 1))};
            $i++;
        }

        return $salt;
    }


}