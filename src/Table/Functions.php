<?php

namespace LeadMax\TrackYourStats\Table;

// class to hold some functions used in tables
class Functions
{

    static function printAssoc($DB_ARRAY, $STRIPES = false)
    {
        if ($STRIPES) {
            $isOdd = 0;
            foreach ($DB_ARRAY as $row => $val) {
                echo "<tr>";
                if ($isOdd == 1) {
                    $print = "class=\"lightGray\"";
                    $isOdd = 0;
                } else {
                    $print = "";
                    $isOdd = 1;
                }
                foreach ($val as $key => $val2) {

                    echo "<td {$print} >".$val2."</td>";
                }

                echo "</tr>";
            }
        } else {

            foreach ($DB_ARRAY as $row => $val) {
                echo "<tr>";

                foreach ($val as $key => $val2) {

                    echo "<td>".$val2."</td>";
                }

                echo "</tr>";
            }
        }
    }

    static function printTable($DB_ARRAY, $STRIPES = false)
    {
        if ($STRIPES) {
            $isOdd = 0;
            for ($i = 0; $i < count($DB_ARRAY); $i++) {
                echo "<tr>";
                if ($isOdd == 1) {
                    $print = "class=\"lightGray\"";
                    $isOdd = 0;
                } else {
                    $print = "";
                    $isOdd = 1;
                }
                for ($k = 0; $k < count($DB_ARRAY[$i]); $k++) {

                    echo "<td {$print} >".$DB_ARRAY[$i][$k]."</td>";
                }

                echo "</tr>";
            }
        } else {

            for ($i = 0; $i < count($DB_ARRAY); $i++) {
                echo "<tr>";

                for ($k = 0; $k < count($DB_ARRAY[$i]); $k++) {

                    echo "<td>".$DB_ARRAY[$i][$k]."</td>";
                }

                echo "</tr>";
            }
        }


    }

}

?>