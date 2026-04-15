<?php

namespace LeadMax\TrackYourStats\Report\Filters;


use Carbon\Carbon;
use LeadMax\TrackYourStats\Table\Assignments;

class ClickLink implements Filter
{

    public $clicksArrayKey;

    public $offerIdArrayKey;

    public $assign;

    public function __construct( $assign, $clicksArrayKey = "Clicks", $offerIdArrayKey = "idoffer")
    {
        $this->assign = $assign;

        $this->clicksArrayKey = $clicksArrayKey;

        $this->offerIdArrayKey = $offerIdArrayKey;
    }

    public function filter($data)
    {
        $i = 0;
        $count = count($data);
        foreach ($data as $key => $row) {
            $i++;
            if (isset($row[$this->clicksArrayKey]) && $i !== $count) {
                $replaced =
                    "<a href=\"/offer/{$row[$this->offerIdArrayKey]}/clicks?d_from={$this->assign->get("d_from", Carbon::today()->format('Y-m-d'))}&d_to={$this->assign->get("d_to", Carbon::today()->format('Y-m-d'))}&dateSelect={$this->assign->get("dateSelect",0)}&filter=affiliate\" >{$row[$this->clicksArrayKey]}</a>";
                $data[$key][$this->clicksArrayKey] = $replaced;
            }
        }

        return $data;
    }


}