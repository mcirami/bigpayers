<?php

namespace App\Support;

class ClickIdCodec
{
    public static array $keys = [
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

    public static function encode($value): string
    {
        $characters = str_split((string) $value);

        foreach ($characters as $index => $character) {
            $characters[$index] = self::$keys[$character][array_rand(self::$keys[$character])];
        }

        return implode('', $characters);
    }

    public static function decode($value): string
    {
        $characters = str_split((string) $value);

        foreach ($characters as $index => $character) {
            foreach (self::$keys as $digit => $keys) {
                if (in_array($character, $keys, true)) {
                    $characters[$index] = $digit;
                    break;
                }
            }
        }

        return implode('', $characters);
    }
}
