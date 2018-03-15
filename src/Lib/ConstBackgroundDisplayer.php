<?php
namespace SniWapa\Lib;

class ConstBackgroundDisplayer
{
    public function __construct(ImageCreator $imageCreator)
    {
        $this->imageCreator = $imageCreator;
    }

    public function showBackground(string $colorString)
    {
        $path = $this->imageCreator->createConstColoredImage($colorString);
        $displayString = Screen::getDisplayString();
        exec("{$displayString} feh --bg-fill {$path}");
    }
}
