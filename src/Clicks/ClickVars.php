<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/9/2017
 * Time: 12:14 PM
 */

namespace LeadMax\TrackYourStats\Clicks;


use LeadMax\TrackYourStats\Offer\Offer;
use LeadMax\TrackYourStats\Offer\RepHasOffer;
use LeadMax\TrackYourStats\User\User;
use PDO;

class ClickVars
{

    public $clickObj;

    public $clickVars = array();

    public $affData;

    public $subVars;

    public $postBackUrl = false;

    public $usersGlobalPostBackUrl;

    public $offerURL = false;

    public $incoming = false;

    public $clickID = 0;

    public function __construct($clickID, $incoming = false)
    {
        if ($incoming) {
            $this->incoming = $incoming;

            //get Affiliate data who's ID is linked to that click, stored as PDO::FETCH_OBJ
            $affData = new User();
            $this->affData = User::SelectOne($_GET["repid"]);


            // gets offer url
            $this->offerURL = Offer::selectOneQuery($_GET["offerid"])->fetch(PDO::FETCH_OBJ)->url;

        } else {
            // Get click info as PDO::FETCH_OBJ
            $this->clickObj = Click::SelectOne($clickID);

            //get Affiliate data who's ID is linked to that click, stored as PDO::FETCH_OBJ
            $affData = new User();
            $this->affData = User::SelectOne($this->clickObj->rep_idrep);


            // gets sub variables that were stored when click was generated, stored as obj
            $this->subVars = ClickVars::selectSubVars($clickID)->fetch(PDO::FETCH_OBJ);

            // gets affiliate specific post back url
            $this->postBackUrl = RepHasOffer::getPostbackURL($this->clickObj->offer_idoffer, $this->affData->idrep);

            $this->usersGlobalPostBackUrl = User::getUsersGlobalPostBackURL($this->clickObj->rep_idrep);

            // gets offer url
            $this->offerURL = Offer::selectOneQuery($this->clickObj->offer_idoffer)->fetch(PDO::FETCH_OBJ)->url;
        }


        $this->clickID = $clickID;


    }

    public function sendToPostBack()
    {


        if ($this->postBackUrl !== "" && $this->postBackUrl !== null) {
            $url = $this->postBackUrl;
        } else {
            if ($this->usersGlobalPostBackUrl !== "" && $this->usersGlobalPostBackUrl !== null) {
                $url = $this->usersGlobalPostBackUrl;
            } else {
                return;
            }
        }


        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);


    }

    public function sendToOffer()
    {
        $this->CHECK_DEBUG();
        send_to($this->offerURL);
    }

    public function processOfferUrl()
    {

    }

    public function processPostBackURL()
    {
        $url = $this->postBackUrl;

        if ($url != "") {
            $url = $this->processTYSVariables($url);

            $url = $this->processSubVars($url);


            $this->postBackUrl = $url;
        }

        if ($this->usersGlobalPostBackUrl !== "") {
            $this->usersGlobalPostBackUrl = $this->processTYSVariables($this->usersGlobalPostBackUrl);
            $this->usersGlobalPostBackUrl = $this->processSubVars($this->usersGlobalPostBackUrl);
        }

    }

    public function isSubVarTag($str)
    {
        if (preg_match("[#sub[1-5]#]", $str)) {
            return true;
        }

        return false;
    }

    public function processIncomingClick()
    {
        $getArray = $_GET;

        $newURL = $this->processTYSVariables($this->offerURL);

        //process Sub Vars
        for ($i = 1; $i <= 5; $i++) {
            if (isset($getArray["sub{$i}"]) && $getArray["sub{$i}"] != "") {
                $newURL = str_replace("#sub{$i}#", $getArray["sub{$i}"], $newURL);
            } else {
                $newURL = str_replace("#sub{$i}#", "", $newURL);
            }
        }


        $newURL = self::checkForBase64($newURL);

        $this->offerURL = $newURL;

    }

    private function CHECK_DEBUG()
    {
        if (isset($_GET["DEBUG"])) {
            dd($this->offerURL);
        }
    }

    public static function checkForBase64($url)
    {
        $start = strpos($url, "<base64>");
        $end = strpos($url, "</base64>");


        if ($start !== false && $end !== false) {
            $urlStartBeforeTag = substr($url, 0, $start);
            $urlAfterTag = substr($url, $end + 9);

            $removedFirstTag = substr($url, strpos($url, "<base64>") + 8);
            $encodeInside = substr($removedFirstTag, 0, strpos($removedFirstTag, "</base64>"));

            $encodeInside = base64_encode($encodeInside);


            $newURL = $urlStartBeforeTag.$encodeInside.$urlAfterTag;

            return self::checkForBase64($newURL);
        } else {
            return $url;
        }
    }


    // Since this is based on the subvars stored with a click id
    // Only to be used with Outgoing traffic  (postback.php) (conversions)
    public function processSubVars($url)
    {

        $storedClickQuery = parse_url($this->subVars->url);


        if (!isset($storedClickQuery["query"])) {
            return $url;
        }

        parse_str($storedClickQuery["query"], $storedClickQuery);

        $postBackUrl = $url;


        //process Sub Vars
        for ($i = 1; $i <= 5; $i++) {
            if (isset($storedClickQuery["sub{$i}"])) {
                $postBackUrl = str_replace("#sub{$i}#", $storedClickQuery["sub{$i}"], $postBackUrl);
            }
        }


        return $postBackUrl;
    }




//    public function processSubVars($url)
//    {
////        $url = str_replace("#", '', $url);
//
//
//
//        $postBackUrl = parse_url($url);
//
//
//        $parsedPostBack = $postBackUrl;
//
//
//        parse_str($postBackUrl["query"], $postBackUrl);
//
//
//        $parseClickUri = parse_url($this->subVars->url);
//
//        var_dump($parseClickUri);
//
//        parse_str($parseClickUri["query"], $parseClickUri);
//
//        var_dump($parseClickUri);
//
//
//        foreach ($postBackUrl as $key => $val) {
//            foreach ($parseClickUri as $key2 => $val2) {
//
//
//                if ($key == $key2)
//                    $postBackUrl[$key] = $val2;
//            }
//        }
//
//
//        $url = http_build_query($postBackUrl);
//
//        if (in_array("http", $parsedPostBack))
//            $url = $parsedPostBack["scheme"] . "://" . $parsedPostBack["host"] . $parsedPostBack["path"] . "?" . $url;
//        else
//            $url = $parsedPostBack["host"] . $parsedPostBack["path"] . "?" . $url;
//
//
//        return $url;
//    }

    public function processTYSVariables($url)
    {

        if ($this->incoming) { //landingpage.php
            $url = str_replace("#affid#", $_GET["repid"], $url);
            $url = str_replace("#offid#", $_GET["offerid"], $url);
            $url = str_replace("#clickid#", UID::encode($this->clickID), $url);
        } else // postback.php
        {
            $url = str_replace("#affid#", $this->clickObj->rep_idrep, $url);
            $url = str_replace("#offid#", $this->clickObj->offer_idoffer, $url);
            $url = str_replace("#clickid#", UID::encode($this->clickObj->idclicks), $url);
        }

        $url = str_replace("#user#", $this->affData->user_name, $url);

        return $url;
    }


    static function processUrlToSubIDArray($url)
    {
        $subIDs = [
            "sub1" => "",
            "sub2" => "",
            "sub3" => "",
            "sub4" => "",
            "sub5" => "",
        ];


        $parseClickUri = parse_url($url);

        if (empty($parseClickUri)) {
            return $subIDs;
        }

        if (isset($parseClickUri["query"]) == false) {
            return $subIDs;
        }

        parse_str($parseClickUri["query"], $parseClickUri);


        foreach ($parseClickUri as $key2 => $val2) {
            switch ($key2) {
                case "sub1":
                    $subIDs["sub1"] = $val2;
                    break;

                case "sub2":
                    $subIDs["sub2"] = $val2;
                    break;

                case "sub3":
                    $subIDs["sub3"] = $val2;
                    break;

                case "sub4":
                    $subIDs["sub4"] = $val2;
                    break;

                case "sub5":
                    $subIDs["sub5"] = $val2;
                    break;

            }

        }

        return $subIDs;

    }

    static function getSubVarArray($click_id)
    {
        $storedQueryString = ClickVars::selectSubVars($click_id)->fetch(\PDO::FETCH_OBJ);

        $storedClickQuery = parse_url($storedQueryString->url);


        if (isset($storedClickQuery["query"]) == false) {
            return [];
        }

        parse_str($storedClickQuery["query"], $storedClickQuery);


        return $storedClickQuery;
    }

    static function selectSubVars($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM click_vars WHERE click_id = :id ";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;

    }

}
