<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/15/2017
 * Time: 2:13 PM
 */

namespace LeadMax\TrackYourStats\Offer;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LeadMax\TrackYourStats\Offer\Rules\Device;
use LeadMax\TrackYourStats\Offer\Rules\Geo;
use LeadMax\TrackYourStats\Offer\Rules\NoneUnique;
use PDO;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class Rules
{

    // all rules found within the passed offer id
    public $rules = array();


    private $ruleObjs = array();

    // offer id
    public $offid = 0;
    
    public $ip;

    function __construct($offid, $ip = null)
    {
        // set our offer id
        $this->offid = $offid;

        $this->ip = $ip;

        // find all rules associated with that offer
        $this->getRules();


        // creates all rule objects with rules found with offer id
        $this->addAllRules();


    }


    public function duplicateRules($newOfferID)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        if ($this->rules !== false) {

            // first organize by rule id


            $rules = array();

            foreach ($this->rules as $key => $rule) {
                if (!isset($rules[$rule["idrule"]])) {
                    $id = $rule["idrule"];

                    // base rule
                    $rules[$id] = [
                        'name' => $rule["name"],
                        "type" => $rule["type"],
                        "redirect_offer" => $rule["redirect_offer"],
                        "is_active" => $rule["is_active"],
                        "deny" => $rule["deny"],
                        'rule_list' => array(),
                    ];


                }
                // add rule list, e.i. country names, device types...
                switch ($rule["type"]) {
                    case "device":
                        $rules[$rule["idrule"]]['rule_list'][] = $rule["device_type"];
                        break;

                    case "geo":
                        $rules[$rule["idrule"]]['rule_list'][] = [
                            "country_name" => $rule["country_name"],
                            'country_code' => $rule["country_code"],
                        ];
                        break;
                }
            }


            // create rules to new offer
            foreach ($rules as $rule) {

                $sql = "INSERT INTO rule (name, offer_idoffer, type, redirect_offer, is_active, deny) VALUES(:name, :offer_idoffer, :type, :redirect_offer, :is_active, :deny)";
                $prep = $db->prepare($sql);
                $prep->bindParam(":name", $rule["name"]);
                $prep->bindParam(":offer_idoffer", $newOfferID);
                $prep->bindParam(":type", $rule["type"]);
                $prep->bindParam(":redirect_offer", $rule["redirect_offer"]);
                $prep->bindParam(":is_active", $rule["is_active"]);
                $prep->bindParam(":deny", $rule["deny"]);

                if ($prep->execute()) {
                    $ruleID = $db->lastInsertId();

                    switch ($rule["type"]) {

                        case "device":
                            $sql = "INSERT INTO device_rule(rule_idrule) VALUES(:ruleID)";
                            $prep = $db->prepare($sql);
                            $prep->bindParam(":ruleID", $ruleID);
                            $prep->execute();

                            $deviceRuleID = $db->lastInsertId();

                            $questionMarks = [];
                            $insertValues = [];
                            foreach ($rule["rule_list"] as $deviceRule) {
                                $questionMarks[] = "(?,?)";
                                $insertValues[] = $deviceRule;
                                $insertValues[] = $deviceRuleID;
                            }

                            $sql = "INSERT INTO device_list(device_type, device_rule_iddevice_rule) VALUES".implode(",",
                                    $questionMarks);
                            $prep = $db->prepare($sql);
                            $prep->execute($insertValues);
                            break;


                        case "geo":
                            $sql = "INSERT INTO geo_rule(rule_idrule) VALUES(:ruleID)";
                            $prep = $db->prepare($sql);
                            $prep->bindParam(":ruleID", $ruleID);
                            $prep->execute();

                            $geoRuleID = $db->lastInsertId();

                            $questionMarks = [];
                            $insertValues = [];
                            foreach ($rule["rule_list"] as $geoRule) {
                                $questionMarks[] = "(?,?,?)";
                                $insertValues[] = $geoRule["country_name"];
                                $insertValues[] = $geoRule["country_code"];
                                $insertValues[] = $geoRuleID;
                            }

                            $sql = "INSERT INTO country_list(country_name, country_code, geo_rule_idgeo_rule) VALUES".implode(",",
                                    $questionMarks);
                            $prep = $db->prepare($sql);
                            $prep->execute($insertValues);
                            break;
                    }
                }

            }

        }
    }


    public function checkAllRules()
    {

        if (empty($this->rules)) {
            return true;
        }

        /*foreach($this->rules as $rule) {
            if ($rule['cap_status']) {
                $cap = $rule['cap'];
                $countryRule = $rule['country_code'];
                $offerId = $rule['offer_idoffer'];

                $tz = 'America/New_York';
                $timeNow = \Illuminate\Support\Carbon::today($tz)->format('Y-m-d');
                $from = $timeNow . " 00:00:00";
                $to = $timeNow . " 23:59:59";

                $carbonFrom = Carbon::createFromFormat('Y-m-d H:i:s', $from, $tz);
                $carbonTo = Carbon::createFromFormat('Y-m-d H:i:s', $to, $tz);
                $dateFrom = $carbonFrom->setTimezone("UTC");
                $dateTo = $carbonTo->setTimezone("UTC");

                $conversions = DB::table('conversions')
                ->whereBetween('first_timestamp', [$dateFrom, $dateTo ])
                ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
                ->where('clicks.offer_idoffer', '=', $offerId)
				->select('ip_address', 'country_code')->get();

                if (count($conversions) > 0) {

                    $count = 0;
                    foreach($conversions as $conversion) {
                        if ($conversion->country_code) {
                            if ($conversion->country_code != "UNKNOWN" &&
                                $conversion->country_code == $countryRule &&
                                $clickCountry == $conversion->country_code) {
                                ++$count;
                            }
                        } else {
                            $geo = preg_replace('/[^a-zA-Z]/', '', ClickGeo::findGeo($conversion['ip_address']));
                            if ($geo['isoCode'] == $countryRule && $clickCountry == $geo['isoCode']) {
                                ++$count;
                            }
                        }
                    }

                    if ($count >= $cap) {
                        $url = $this->buildRedirectUrl($rule['redirect_offer']);
                        send_to($url); 
                    };
                }
            }
        }*/

        foreach ($this->ruleObjs as $key => $rule) {

			$ruleCheck = $rule->checkRules();
            if (!$ruleCheck) {
                $newRules = new Rules($rule->redirectOffer, $this->ip);
                if ($newRules->checkAllRules()) {
                    $url = $this->buildRedirectUrl($rule->redirectOffer);
                    send_to($url);
                }
            }
        }

        return true;
    }


    private function addAllRules()
    {
        // none unique
        $this->addToList(new NoneUnique($this->rules));

        // geo
        $this->addToList(new Geo($this->rules));

        // device
        $this->addToList(new Device($this->rules));
    }


    // add a rule class to our ruleOjbs array..
    private function addToList($ruleObj)
    {
        $this->ruleObjs[] = $ruleObj;
    }

    //get all rules associated with passed offerid
    private function getRules()
    {
        $this->rules = $this->getRulesQuery()->fetchAll(PDO::FETCH_ASSOC);
    }


    // testing shit, used to print all rules to html somewhat readable
    public function printRules()
    {
        echo "<table>";
        echo "<thead>
            <tr>
               <th>Key</th>
               <th>Column</th>
               <th>Data</th>
</tr>

</thead>";
        echo "<tbody>";
        foreach ($this->rules as $key => $val) {
            foreach ($val as $key2 => $val2) {
                echo "<tr>";
                echo "<td>{$key}</td>";
                echo "<td>{$key2}</td>";
                echo "<td>{$val2}</td>";
                echo "</tr>";
            }

        }
        echo "</tbody>";
        echo "</table>";
    }

    public function printTable()
    {
        $this->rules = $this->getBaseRulesQuery()->fetchAll(PDO::FETCH_ASSOC);


        foreach ($this->rules as $key => $val) {
            echo "<tr>";


            echo "<td id='{$val["idrule"]}'>{$val["name"]}</td>";

            switch ($val["type"]) {
                case "geo":
                    echo "<td>Geo</td>";
//                       echo"<td><a href='javascript:void(0);'>View List</a></td>";
                    break;

                case "device":
                    echo "<td>Device</td>";
//                       echo "<td><a href='javascript:void(0);'>View List</a></td>";
                    break;

                case "none_unique":
                    echo "<td>None Unique</td>";
                    break;
            }

            if ($val["type"] == "none_unique") {
                echo "<td><span >N/A</span></td>";
            } else {
                if ($val["deny"] == "0") {
                    echo "<td><span style='color:darkgreen'>ALLOW</span></td>";
                } else {
                    echo "<td><span style='color:red'>DENY</span></td>";
                }
            }

            $redirectOffer = Offer::selectOneQuery($val["redirect_offer"])->fetch(PDO::FETCH_OBJ);
            echo "<td>{$val["redirect_offer"]} - {$redirectOffer->offer_name}</td>";


            if ($val["is_active"] == 1) {
                echo "<td><span style='color:darkgreen'>ACTIVE</span></td>";
            } else {
                echo "<td><span style='color:red'>IN-ACTIVE</span></td>";
            }


            if ($val["type"] == "none_unique") {
                echo "<td><a href='/edit_none_unique.php?id={$val["idrule"]}'><img src='images/icons/pencil.png' alt='Edit'></a></td>";
            } else {
                echo "<td><a onclick=\"editRule({$val["idrule"]},'{$val["type"]}')\" href='javascript:void(0);'><img src='images/icons/pencil.png' alt='Edit'></a></td>";
            }

            echo "</tr>";
        }
    }

    // query to find all rules associated with an offer
    private
    function getRulesQuery()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rule

                LEFT JOIN geo_rule on geo_rule.rule_idrule = rule.idrule
                LEFT OUTER JOIN country_list ON country_list.geo_rule_idgeo_rule = geo_rule.idgeo_rule

                LEFT JOIN device_rule ON device_rule.rule_idrule = rule.idrule 
                LEFT OUTER JOIN device_list ON device_list.device_rule_iddevice_rule = device_rule.iddevice_rule


      WHERE rule.offer_idoffer = :offid 
      
   
      ";

        $prep = $db->prepare($sql);

        $prep->bindParam(":offid", $this->offid);

        $prep->execute();

        return $prep;
    }


    private function getBaseRulesQuery()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rule

                LEFT JOIN geo_rule on geo_rule.rule_idrule = rule.idrule
                LEFT JOIN device_rule ON device_rule.rule_idrule = rule.idrule 

      WHERE rule.offer_idoffer = :offid";

        $prep = $db->prepare($sql);

        $prep->bindParam(":offid", $this->offid);

        $prep->execute();

        return $prep;
    }

    private function buildRedirectUrl($offid)
    {
        $url = "http://".$_SERVER["HTTP_HOST"];

        $url .= "/?";

        $first = true;
        foreach ($_GET as $key => $val) {

            if ($key == "offerid") {
                if ($first) {
                    $url .= "{$key}={$offid}";
                } else {
                    $url .= "&{$key}={$offid}";
                }
            } else {
                if ($first) {
                    $url .= "{$key}={$val}";
                } else {
                    $url .= "&{$key}={$val}";
                }

            }

            $first = false;
        }

        return $url;

    }


}