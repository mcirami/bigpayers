<?php

namespace LeadMax\TrackYourStats\Report\Filters;

class DollarSign implements Filter
{

    private $toDollarSign = array();

    public function __construct(array $dollarSignThese)
    {
        $this->toDollarSign = $dollarSignThese;
    }

    public function dollarSignThese(array $arrayKeys)
    {
        $this->toDollarSign = $arrayKeys;
    }

    public static function dollarSignNum($num)
    {

        $num = number_format((double)$num, 2);

        return "$" . $num;
    }


    public function filter($report)
    {
        foreach ($report as $key => $val) {
            foreach ($val as $key2 => $val2) {
                if (in_array($key2, $this->toDollarSign)) {
                    $val[$key2] = self::dollarSignNum($val2);
                    $report[$key] = $val;
                }
            }
        }

        return $report;
    }

}