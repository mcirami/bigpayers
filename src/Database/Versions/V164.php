<?php

namespace LeadMax\TrackYourStats\Database\Versions;

use Carbon\Carbon;
use LeadMax\TrackYourStats\Clicks\ClickVars;
use LeadMax\TrackYourStats\Database\Version;

//@deprecated
class V164 extends Version
{

    /*
     * This was a version to fix a bug on click_vars saving to database, the sub5 variable was being assigned to all subs 1-5
     * Fetches click_vars in that time period where bug was active and updates the sub variables accordingly
     *
     *
     * This update was disabled (update() and verifyUpdate() both return true hard coded)
     * because of how expensive it is and its no longer needed.
     *
     */

    public $dateFrom = '2018-4-19';

    public $dateTo = '2018-4-23';


    private function generateDateRange(Carbon $start, Carbon $end)
    {
        $dates = [];
        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    public function getVersion()
    {
        return 1.64;
    }

    private function createCarbonInstance($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date);
    }

    /**
     * This method simply returns true, because it is called from within the parent methods and
     * no other convenient way to work around this exists at the moment
     *
     * @deprecated
     * @return bool
     */
    public function update()
    {

        return true;
        $dateRange = $this->generateDateRange($this->createCarbonInstance($this->dateFrom),
            $this->createCarbonInstance($this->dateTo));
        foreach ($dateRange as $day) {
            $start = $day." 00:00:00";
            $end = $day." 23:59:59";
            if ($this->updateInDateRange($start, $end) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method simply returns true, because it is called from within the parent methods and
     * no other convenient way to work around this exists at the moment
     *
     * @deprecated
     * @return bool
     */
    public function verifyUpdate(): bool
    {
        return true;

        $dateRange = $this->generateDateRange($this->createCarbonInstance($this->dateFrom),
            $this->createCarbonInstance($this->dateTo));
        foreach ($dateRange as $day) {
            $start = $day." 00:00:00";
            $end = $day." 23:59:59";


            if ($this->verifyInDateRange($start, $end) == false) {
                return false;
            }
        }


        return true;
    }

    private function updateInDateRange($startDate, $endDate)
    {
        $clickVars = $this->fetchClickVars($startDate, $endDate);


        foreach ($clickVars as $clickVar) {
            $subVariables = ClickVars::processUrlToSubIDArray($clickVar["url"]);

            if ($this->doesSubVarsMatch($subVariables, $clickVar) == false) {
                $this->updateClickVar($clickVar["click_id"], $subVariables);
            }
        }


        return true;
    }

    private function verifyInDateRange($startDate, $endDate)
    {
        $clickVars = $this->fetchClickVars($startDate, $endDate);


        foreach ($clickVars as $click) {
            $subVars = ClickVars::processUrlToSubIDArray($click["url"]);
            if ($this->doesSubVarsMatch($subVars, $click) == false) {
                return false;
            }
        }

        return true;
    }


    private function doesSubVarsMatch($arrayOne, $arrayTwo)
    {
        for ($i = 1; $i <= 5; $i++) {
            $sub = "sub{$i}";
            if ($arrayOne[$sub] !== $arrayTwo[$sub]) {
                return false;
            }
        }

        return true;
    }


    private function updateClickVar($id, $subArray)
    {
        $sql = "UPDATE click_vars SET ";

        for ($i = 1; $i <= 5; $i++) {
            $sql .= " sub{$i} = :sub{$i}";

            if ($i !== 5) {
                $sql .= ", ";
            }
        }

        $sql .= " WHERE click_id = :click_id";


        $prep = $this->getDB()->prepare($sql);

        for ($i = 1; $i <= 5; $i++) {
            $sub = isset($subArray["sub{$i}"]) ? $subArray["sub{$i}"] : "";
            $prep->bindValue(":sub{$i}", $sub);
        }

        $prep->bindParam(":click_id", $id);

        return $prep->execute();
    }


    private function fetchClickVars($dateFrom, $dateTo)
    {

        $sql = "SELECT * FROM click_vars INNER JOIN clicks ON clicks.idclicks = click_vars.click_id WHERE clicks.first_timestamp BETWEEN :dateFrom AND :dateTo";

        $prep = $this->getDB()->prepare($sql);
        $prep->bindParam(":dateFrom", $dateFrom);
        $prep->bindParam(":dateTo", $dateTo);

        $prep->execute();

        return $prep->fetchAll(\PDO::FETCH_ASSOC);
    }


}