<?php

namespace SniWapa\Lib;

class Screen
{
    public static function getDisplayString()
    {
        $string = trim(`w $(id -un) | awk 'NF > 7 && $2 ~ /tty[0-9]+/ {print $3; exit}'`);

        if (preg_match('@:\d@', $string)) {
            return "DISPLAY={$string}.0";
        } else {
            return "DISPLAY=1.0";
        }
    }
}
