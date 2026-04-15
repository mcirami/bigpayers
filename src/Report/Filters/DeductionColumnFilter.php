<?php

namespace LeadMax\TrackYourStats\Report\Filters;


class DeductionColumnFilter implements Filter
{



    public $deductionArrayKey;

    public function __construct($deductionArrayKey = 'Deductions')
    {
        $this->deductionArrayKey = $deductionArrayKey;
    }

    public function filter($data)
    {
        foreach ($data as &$row) {
            if (isset($row[$this->deductionArrayKey]) && $row[$this->deductionArrayKey] > 0) {
                $row[$this->deductionArrayKey] = -1 * $row[$this->deductionArrayKey];
            }
        }


        return $data;
    }

}