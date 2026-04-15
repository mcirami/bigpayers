<?php namespace LeadMax\TrackYourStats\Clicks;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/29/2017
 * Time: 2:30 PM
 */
class UID
{

    static $keys = [
        0 => ['a', 'R', 'y', 'K'],
        1 => ['t', 's', 'c', 'X', 'j'],
        2 => ['D', 'C', 'A', 'J', 'i'],
        3 => ['u', 'E', 'N', 'M', 'v', 'd'],
        4 => ['Q', 'b', 'r', 'k'],
        5 => ['Z', 'F', 'f', 'I', 'n', 'h'],
        6 => ['w', 'H', 'l', 'z', 'q', 'g'],
        7 => ['B', 'p', 'W', 'O', 'm'],
        8 => ['U', 'Y', 'e', 'P', 'S', 'x'],
        9 => ['L', 'V', 'G', 'o'],
    ];


    static function encode($str)
    {
        $array = str_split($str);
        for ($i = 0; $i < count($array); $i++) {
            $array[$i] = self::$keys[$array[$i]][rand(0, (count(self::$keys[$array[$i]]) - 1))];
        }

        return implode("", $array);
    }

    static function decode($str)
    {

        $array = str_split($str);
        foreach ($array as $key => $val) {
            foreach (self::$keys as $key2 => $val2) {
                if (in_array($val, $val2)) {
                    $array[$key] = $key2;
                }
            }
        }

        return implode("", $array);


    }


}