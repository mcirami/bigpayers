<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/24/2017
 * Time: 4:11 PM
 */

namespace LeadMax\TrackYourStats\Table;

// original report base class, has epc functions, created another 'BaseReport' class because wanted to change some stuff


use LeadMax\TrackYourStats\System\Session;

class ReportBase
{

    //must be public so other classes can see
    public $report = array();


    public static function createUserTooltip($userName, $userID)
    {

        $padding = "style='margin-left:5px;'";

        $logintouser = "<a href='#'  onclick='adminLogin({$userID});'>Login</a>";
        $edituser = "<a {$padding} href='/aff_update.php?idrep={$userID}' target='_blank'>edit</a>";

        $returnstr =
            "<a href=\"#\" data-toggle=\"popover\" data-trigger=\"focus\"
                placement=\"top\" data-html=\"true\"
                data-content=\"";
        if (session::permissions()->can("create_affiliates")) {
            $returnstr .= "
            {$logintouser}{$edituser}";
        }


        $returnstr .= " \">{$userName}";

        return $returnstr;
    }


    public function EPC()
    {

        foreach ($this->report as $row => $key) {


            if ($key['Clicks'] != 0) {
                $this->report [$row]["EPC"] = $this->roundEPC(($this->report [$row]['Revenue'] / $key['Clicks']));
            } else {
                $this->report [$row]["EPC"] = 0;
            }


        }

    }

    public
    static function dollarSignNum(
        $num
    ) {
        $num = number_format((double)$num, 2);

        return "$".$num;
    }

    public
    function totalAll(
        $excludes = array()
    ) {
        if (empty($this->report)) {
            return false;
        }
        $totals = array();

        foreach ($excludes as $val) {
            $totals[$val] = null;
        }

        foreach ($totals as $key => $val) {
            $totals[$key] = "TOTAL";
            break;
        }

        foreach ($this->report as $key => $row) {
            foreach ($row as $key2 => $val) {
                if (!in_array($key2, $excludes)) {
                    if (isset($totals[$key2]) && is_numeric($val)) {
                        $totals[$key2] += $val;
                    } else {
                        $totals[$key2] = $val;
                    }

                }
            }
        }


        array_push($this->report, $totals);

    }

    public
    function totalAllCustom(
        $toTotal,
        $excludes = array()
    ) {
        if (empty($toTotal)) {
            return false;
        }
        $totals = array();

        foreach ($excludes as $val) {
            $totals[$val] = null;
        }

        foreach ($totals as $key => $val) {
            $totals[$key] = "TOTAL";
            break;
        }

        foreach ($toTotal as $key => $row) {
            foreach ($row as $key2 => $val) {
                if (!in_array($key2, $excludes)) {
                    if (isset($totals[$key2]) && is_numeric($val)) {
                        $totals[$key2] += $val;
                    } else {
                        $totals[$key2] = $val;
                    }
                }
            }
        }


        array_push($toTotal, $totals);

        return $toTotal;

    }


    public
    function dollarSignThese(
        $toDollarSign
    ) {
        foreach ($this->report as $key => $val) {
            foreach ($val as $key2 => $val2) {
                if (in_array($key2, $toDollarSign)) {
                    $val[$key2] = self::dollarSignNum($val2);
                    $this->report[$key] = $val;

                }
            }
        }
    }


    public
    function dollarSignTheseCustom(
        $report,
        $toDollarSign
    ) {
        foreach ($report as $key => $val) {
            foreach ($val as $key2 => $val2) {
                if (in_array($key2, $toDollarSign)) {
                    $val[$key2] = self::dollarSignNum($val2);
                    $report[$key] = $val;

                }
            }
        }

        return $report;
    }

//prints multi-dimential array
    public
    function printReport(
        $STRIPES = false
    ) {
        if ($STRIPES) {

            $isOdd = 0;

            foreach ($this->report as $row => $key) {
                echo "<tr>";
                if ($isOdd == 1) {
                    $print = "class=\"lightGray\"";
                    $isOdd = 0;
                } else {
                    $print = "";
                    $isOdd = 1;
                }

                for ($k = 0; $k < count($key); $k++) {
                    echo "<td {$print} >".$key[$k]."</td>";
                }


                echo "</tr>";
            }


        } else {

            foreach ($this->report as $row => $key) {
                echo "<tr>";
                for ($k = 0; $k < count($key); $k++) {
                    echo "<td>".$key[$k]."</td>";
                }
                echo "</tr>";
            }
        }


    }


//input float value to round to nearest 100th place decimal
    public
    function roundEPC(
        $val
    ) {
        if (($p = strpos($val, '.')) !== false) {

            if (floatval(substr($val, 4, $p)) >= 5) {

                $val += 0.01;
                $val = $this->truncate($val, 2);
            } else {
                $val = $this->truncate($val, 2);

            }


        }

        return $val;
    }


//truncate floating point value
    public
    function truncate(
        $val,
        $f = "0"
    ) {
        if (($p = strpos($val, '.')) !== false) {
            $val = floatval(substr($val, 0, $p + 1 + $f));
        }

        return $val;
    }
}
