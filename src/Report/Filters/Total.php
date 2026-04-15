<?php

namespace LeadMax\TrackYourStats\Report\Filters;


class Total implements Filter
{

    public $totalThese = array();

    private $totalColumn;

    /**
     * Total constructor.
     * @param $totalThese
     * @param bool|array $totalColumn True if you want same columns as $totalThese, array for different keys
     */
    public function __construct($totalThese, $totalColumn = false)
    {
        $this->totalThese = $totalThese;

        $this->totalColumn = ($totalColumn === true) ? $totalThese : $totalColumn;
    }


    public function filter($report)
    {
        $totals = array();

        if ($firstRow = array_first($report)) {
            foreach ($firstRow as $name => $value) {
                $totals[$name] = '';
            }

            $totals[array_keys($totals)[0]] = 'TOTAL';
        }


        if ($this->totalColumn) {
            foreach ($report as $key => $row) {
                $rowTotal = 0;
                foreach ($row as $rowkey => $val) {
                    if (in_array($rowkey, $this->totalColumn) &&
                        is_numeric($val)) {
                        $rowTotal += $val;
                    }
                }

                $report[$key]["TOTAL"] = $rowTotal;
            }
        }

        foreach ($report as $key => $row) {
            foreach ($row as $name => $value) {
                if (in_array($name, $this->totalThese)) {
                    if (!isset($totals[$name]) || $totals[$name] == '') {
                        $totals[$name] = 0;
                    }
                    if (is_int($value) || is_float($value) || $name == "UniqueClicks") {
                        $totals[$name] += $value;
                    }
                }
            }
        }

        array_push($report, $totals);

        return $report;
    }


}