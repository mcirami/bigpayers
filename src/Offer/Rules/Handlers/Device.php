<?php namespace LeadMax\TrackYourStats\Offer\Rules\Handlers;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/28/2017
 * Time: 12:40 PM
 */

use PDO;

class Device
{
    public $type = "device";

    public $ruleID = 0;

    public $rules = array();

    public $postData = [];

    public $offerID = 0;

    public $ruleName = "";

    public $redirectOffer = 0;

    public $deny = 0;

    public $deviceList = array();

    public $capStatus = 0;

    public $capAmount = 0;

    function __construct($args)
    {
        // if we're editing a device rule
        if (is_string($args)) {

            $this->ruleID = $args;

            $this->getRules();
            $this->offerID = $this->rules[0]["offer_idoffer"];
            $this->capStatus = $this->rules[0]["cap_status"];
            $this->capAmount = $this->rules[0]["cap"];

        } else  // if we're creating a new device rule
        {

            $this->postData = $args;

            $this->offerID = $args[0];

            $this->ruleName = $args[1];

            $this->redirectOffer = $args[2];

            $this->deny = $args[3];

            $this->capAmount = $args[4];

            $this->capStatus = $args[5];
            

            for ($i = 6; $i < count($args); $i++) {
                $this->deviceList[] = $args[$i];
            }

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
        $ruleData->capAmount = (int)$ruleData->capAmount;
        $ruleData->capStatus = (int)$ruleData->capStatus;

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        try {

            $db->beginTransaction();

            //Update rule and geo_rule
            $sql = "UPDATE rule
                    SET rule.name = :name, rule.redirect_offer = :redirect_offer,  rule.is_active = :is_active, rule.deny = :deny, rule.cap = :cap, rule.cap_status = :capStatus 
                    
                    WHERE rule.idrule = :ruleID  ";


            $prep = $db->prepare($sql);
            $prep->bindParam(":name", $ruleData->name);
            $prep->bindParam(":redirect_offer", $ruleData->redirectOffer);
            $prep->bindParam(":is_active", $ruleData->is_active);
            $prep->bindParam(":deny", $ruleData->deny);
            $prep->bindParam(":ruleID", $ruleData->ruleID);
            $prep->bindParam(":cap", $ruleData->capAmount);
            $prep->bindParam(":capStatus", $ruleData->capStatus);
            $prep->execute();


            // Get geo_rule ID (we need this for country_list)
            $sql = "SELECT iddevice_rule FROM device_rule WHERE rule_idrule = :ruleID";
            $prep = $db->prepare($sql);

            $prep->bindParam(":ruleID", $ruleData->ruleID);
            $prep->execute();

            $deviceRuleID = $prep->fetch(PDO::FETCH_NUM)[0];

            // Delete old countries tied to the geo rule
            $sql = "DELETE FROM device_list WHERE device_rule_iddevice_rule = :ruleID";
            $prep = $db->prepare($sql);
            $prep->bindParam(":ruleID", $deviceRuleID);

            $prep->execute();


            $insertValues = array();
            //start at two because thats where country arrays are
            for ($i = 0; $i < count($countryList); $i++) {


                $questionMarks[] = "(?,?)";
                $vals = array();
                $vals[] = $countryList[$i];
                $vals[] = $deviceRuleID;

                $insertValues = array_merge($insertValues, $vals);


            }


            $sql = 'INSERT INTO device_list (device_type,  device_rule_iddevice_rule) VALUES '.implode(',',
                    $questionMarks);

            $prep = $db->prepare($sql);

            $prep->execute($insertValues);


            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die($e);
        }


    }


    public function dumpRuleInfo()
    {
        echo json_encode($this->parseRuleInfo());
    }

    public function dumpDeviceList()
    {
        $this->parseDevices();
        echo json_encode($this->deviceList);
    }


    private function parseRuleInfo()
    {
        $rule = $this->rules[0];

        return [
            'name'          => $rule["name"],
            'redirectOffer' => $rule["redirect_offer"],
            'is_active'     => $rule["is_active"],
            'deny'          => $rule["deny"],
            'capAmount'     => $rule["cap"],
            'capStatus'     => $rule["cap_status"]
        ];
    }


    private function parseDevices()
    {

        foreach ($this->rules as $key => $val) {
            if ($val["device_type"] !== "") {
                $this->deviceList[] = $val["device_type"];
            }
        }
    }


    public function getRules()
    {
        $this->rules = $this->queryGetRules()->fetchAll(PDO::FETCH_ASSOC);
    }


    private function queryGetRules()
    {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rule 
        INNER JOIN device_rule ON device_rule.rule_idrule = rule.idrule
        LEFT OUTER JOIN device_list on device_list.device_rule_iddevice_rule = device_rule.iddevice_rule 
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

            $sql = "INSERT INTO rule (name, offer_idoffer, type, redirect_offer, deny, cap, cap_status) VALUES(:name, :offerID, :type, :redirect_offer, :deny, :cap, :cap_status)";

            $prep = $db->prepare($sql);


            $prep->bindParam(":name", $this->ruleName);
            $prep->bindParam(":offerID", $this->offerID);
            $prep->bindParam(":type", $this->type);
            $prep->bindParam(":redirect_offer", $this->redirectOffer);
            $prep->bindParam(":deny", $this->deny);
            $prep->bindParam(":cap", $this->capAmount);
            $prep->bindParam(":cap_status", $this->capStatus);
            $prep->execute();

            $ruleID = $db->lastInsertId();

            $sql = "INSERT INTO device_rule (rule_idrule) VALUES(:ruleID)";

            $prep = $db->prepare($sql);

            $prep->bindParam(":ruleID", $ruleID);
            $prep->execute();

            $deviceRuleID = $db->lastInsertId();


            $insertValues = array();
            //start at two because thats where country arrays are
            for ($i = 0; $i < count($this->deviceList); $i++) {


                $questionMarks[] = "(?,?)";
                $vals = array();
                $vals[] = $this->deviceList[$i];
                $vals[] = $deviceRuleID;

                $insertValues = array_merge($insertValues, $vals);


            }


            $sql = 'INSERT INTO device_list (device_type, device_rule_iddevice_rule) VALUES '.implode(',',
                    $questionMarks);

            $prep = $db->prepare($sql);

            $prep->execute($insertValues);


            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            die($e);
        }


    }

}