<?php

namespace SniWapa\Lib;

use SniWapa\Entity\Dimension;

class ImagePercentage
{
    public function getNewDim(
        Dimension $picDim,
        Dimension $resDim,
        int $width,
        int $height
    ): Dimension
    {
        $w = $picDim->getWidth();
        $h = $picDim->getHeight();

        $allowedWidth  = $resDim->getWidth()  * $width  / 100.0;
        $allowedHeight = $resDim->getHeight() * $height / 100.0;

        $factor = min($allowedWidth / $w, $allowedHeight / $h);

        return new Dimension($w * $factor, $h * $factor);
    }
}
