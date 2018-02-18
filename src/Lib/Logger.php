<?php

namespace SniWapa\Lib;

class Logger
{
    public static function log($string)
    {
        $string = (new \DateTime())->format('H:i:s d.m.Y  ') . $string;
        file_put_contents(
            '/mnt/home_daten/Projekte/wapa/test.log',
            $string,
            FILE_APPEND
        );
    }
}
