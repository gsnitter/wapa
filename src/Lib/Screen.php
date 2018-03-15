<?php

namespace SniWapa\Lib;

class Screen
{
    public static function getDisplayString()
    {
        $string = trim(`w $(id -un) | awk 'NF > 7 && $2 ~ /tty[0-9]+/ {print $3; exit}'`);

        // Laut https://faq.i3wm.org/question/6247/assigning-individual-wallpaper-to-monitors.1.html
        // sollte das eigentlich nur den Hintergrund für den ersten Screen ändern.
        // Woanders hieß es, man kann auch einfach sowas we feh --bg-center /first/path /second/path verwenden.
        if (preg_match('@:\d@', $string)) {
            return "DISPLAY={$string}.0";
        } else {
            return "DISPLAY=1.0";
        }
    }

    public static function twoMonitorsConnected(): bool
    {
        $string = trim(`w $(id -un) | awk 'NF > 7 && $2 ~ /tty[0-9]+/ {print $3; exit}'`);
        $number = intval(`DISPLAY={$string} xrandr -q | grep ' connected' | wc -l`);

        return ($number == 2);
    }
}
