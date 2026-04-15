<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 11:26 AM
 */

namespace LeadMax\TrackYourStats\Offer\Rules\Handlers;

use Illuminate\Support\Facades\Log;
use PDO;


class Geo
{

    public $type = "geo";

    public $ruleID = 0;

    public $rules = array();

    public $postData = [];

    public $offerID = 0;

    public $ruleName = "";

    public $redirectOffer = 0;

    public $deny = 0;


    function __construct($args)
    {
        // if we're editing a geo rule
        if (is_string($args)) {
            $this->ruleID = $args;
            $this->getRules();
            $this->offerID = $this->rules[0]["offer_idoffer"];
        } else  // if we're creating a new geo rule
        {

            $this->postData = $args;

            $this->offerID = $args[0];

            $this->ruleName = $args[1];

            $this->redirectOffer = $args[2];

            $this->deny = $args[3];
            if ($this->deny == true) {
                $this->deny = 1;
            } else {
                $this->deny = 0;
            }
        }


    }


    public function updateRule($ruleData, $countryList)
    {

        $ruleData->ruleID = (int)$ruleData->ruleID;
        $ruleData->is_active = (int)$ruleData->is_active;
        $ruleData->deny = (int)$ruleData->deny;
        $ruleData->redirectOffer = (int)$ruleData->redirectOffer;

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        try {

            $db->beginTransaction();

            //Update rule and geo_rule
            $sql = "UPDATE rule
                    SET rule.name = :name, rule.redirect_offer = :redirect_offer,  rule.is_active = :is_active, rule.deny = :deny 
                    
                    WHERE rule.idrule = :ruleID  ";


            $prep = $db->prepare($sql);
            $prep->bindParam(":name", $ruleData->name);
            $prep->bindParam(":redirect_offer", $ruleData->redirectOffer);
            $prep->bindParam(":is_active", $ruleData->is_active);
            $prep->bindParam(":deny", $ruleData->deny);
            $prep->bindParam(":ruleID", $ruleData->ruleID);
            $prep->execute();


            // Get geo_rule ID (we need this for country_list)
            $sql = "SELECT idgeo_rule FROM geo_rule WHERE rule_idrule = :ruleID";
            $prep = $db->prepare($sql);

            $prep->bindParam(":ruleID", $ruleData->ruleID);
            $prep->execute();

            $geoRuleID = $prep->fetch(PDO::FETCH_NUM)[0];

            // Delete old countries tied to the geo rule
            $sql = "DELETE FROM country_list WHERE geo_rule_idgeo_rule = :ruleID";
            $prep = $db->prepare($sql);
            $prep->bindParam(":ruleID", $geoRuleID);

            $prep->execute();

            $insertValues = array();
            //start at two because thats where country arrays are
            for ($i = 0; $i < count($countryList); $i++) {

                if (is_array($countryList[$i])) {
                    $questionMarks[] = "(?,?,?,?,?)";
                    $vals = array_values($countryList[$i]);
                    $vals[] = $geoRuleID;

                    $insertValues = array_merge($insertValues, $vals);
                }


            }

            $sql = 'INSERT INTO country_list (country_code, country_name, cap_status, cap, geo_rule_idgeo_rule) VALUES '.implode(',',
                    $questionMarks);

            $prep = $db->prepare($sql);

            $prep->execute($insertValues);


            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die($e);
        }


    }


    public function dumpCountryCodes()
    {
        echo json_encode($this->parseCountryCodes());
    }

    public function dumpRuleInfo()
    {
        echo json_encode($this->parseRuleInfo());
    }

    private function parseRuleInfo()
    {
        $rule = $this->rules[0];

        return [
            'name' => $rule["name"],
            'redirectOffer' => $rule["redirect_offer"],
            'is_active' => $rule["is_active"],
            'deny' => $rule["deny"],
        ];
    }


    private function parseCountryCodes()
    {
        $countries = array();


        foreach ($this->rules as $rule) {
            $object = [
                'country_code'  => $rule["country_code"],
                'cap_status'    => $rule["cap_status"],
                'cap'           => $rule["cap"]
            ];
            $countries[] = $object;

        }
        return $countries;

    }


    public function getRules()
    {
        $this->rules = $this->queryGetRules()->fetchAll(PDO::FETCH_ASSOC);
       
    }


    private function queryGetRules()
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rule
        INNER JOIN geo_rule ON geo_rule.rule_idrule = rule.idrule
        LEFT OUTER JOIN country_list on country_list.geo_rule_idgeo_rule = geo_rule.idgeo_rule 
        WHERE rule.idrule = :ruleID";

        $prep = $db->prepare($sql);

        $prep->bindParam(":ruleID", $this->ruleID);

        $prep->execute();

        return $prep;
    }


    public function createRule()
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        try {

            $db->beginTransaction();

            $sql = "INSERT INTO rule (name, offer_idoffer, type, redirect_offer, deny) VALUES(:name, :offerID, :type, :redirect_offer, :deny)";

            $prep = $db->prepare($sql);


            $prep->bindParam(":name", $this->ruleName);
            $prep->bindParam(":offerID", $this->offerID);
            $prep->bindParam(":type", $this->type);
            $prep->bindParam(":redirect_offer", $this->redirectOffer);
            $prep->bindParam(":deny", $this->deny);
            $prep->execute();

            $ruleID = $db->lastInsertId();

            $sql = "INSERT INTO geo_rule (rule_idrule) VALUES(:ruleID)";

            $prep = $db->prepare($sql);

            $prep->bindParam(":ruleID", $ruleID);
            $prep->execute();

            $geoRuleID = $db->lastInsertId();


            $insertValues = array();
            
            //start at two because thats where country arrays are
            for ($i = 0; $i < count($this->postData); $i++) {

                if (is_array($this->postData[$i])) {
                    $questionMarks[] = "(?,?,?,?,?)";
                    $vals = array_values($this->postData[$i]);
                    
                    $vals[] = $geoRuleID;

                    $insertValues = array_merge($insertValues, $vals);
                }


            }

            $sql = 'INSERT INTO country_list (country_code, country_name, cap_status, cap, geo_rule_idgeo_rule ) VALUES '.implode(',',
                    $questionMarks);

            $prep = $db->prepare($sql);
            
            $prep->execute($insertValues);


            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            Log::info("Error: " . print_r($e, true));
            die($e);
        }


    }


}