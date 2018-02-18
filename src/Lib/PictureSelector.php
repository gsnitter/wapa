<?php

namespace SniWapa\Lib;

/**
 * This class chooses a more or less random picture to show.
 */
class PictureSelector
{
    private $pathes;

    public function __construct()
    {
        $this->pathes = glob(getenv('IMAGE_SOURCE') . '/*');
    }

    public function chooseOne(): string
    {
        $key = rand(0, count($this->pathes) - 1);
        return $this->pathes[$key];
    }
}
