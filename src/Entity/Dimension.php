<?php

namespace SniWapa\Entity;

use SniWapa\Lib\Logger;
use SniWapa\Lib\Screen;

class Dimension
{
    private $width;
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function __toString()
    {
        return "{$this->width}x{$this->height}";
    }

    public static function createByImage(string $path): Dimension
    {
        // $widthString = `identify $path -auto-orient`;
        $widthString = `convert $path -auto-orient -format "%w x %h" info:`;
        $dimensions = preg_match('@(\d+)\s*x\s*(\d+)@', $widthString, $matches);

        return new Dimension(intval($matches[1]), intval($matches[2]));
    }

    public static function createByResolution(): Dimension
    {
        $displayString = Screen::getDisplayString();
        $string = `{$displayString} xdpyinfo  | grep 'dimensions:'`;
        preg_match('/dimensions:\s+(\d+)x(\d+)/', $string, $matches);

        if (count($matches) != 3) {
            throw new \Exception("Resolution string {$string} not parseable.");
        }

        $x = intval($matches[1]);
        if (Screen::twoMonitorsConnected()) {
            $x = $x/2;
        }

        return new Dimension($x, intval($matches[2]));
    }
}
