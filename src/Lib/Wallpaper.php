<?php

namespace SniWapa\Lib;

use SniWapa\Entity\Dimension;
use SniWapa\Lib\Filesystem;
use SniWapa\Lib\Logger;

class Wallpaper
{
    /** @var ImageCreator $creator */
    private $creator;

    /** @var Filesystem $fs */
    private $fs;

    public function __construct(ImageCreator $creator, Filesystem $fs)
    {
        $this->creator = $creator;
        $this->fs = $fs;
    }

    public function prepare(string $imageIn): string
    {
        $outPath = $this->getTargetPath($imageIn);

        if (!is_readable($outPath)) {
            $buildPath = $this->creator->create($imageIn);
            $this->fs->mkdir($this->getImageCachePath()); 
            rename($buildPath, $outPath);
        }

        return $outPath;
    }

    public function show(string $imageIn)
    {
        $displayString = Screen::getDisplayString();
        exec("{$displayString} feh --bg-fill " . $this->prepare($imageIn));
    }

    public function getTargetPath(string $imageIn): string
    {
        $hash = sha1($imageIn);
        $ext = pathinfo($imageIn, PATHINFO_EXTENSION);

        return $this->getImageCachePath() . "/{$hash}.{$ext}";
    }

    private function getImageCachePath(): string
    {
        return getenv('IMAGE_CACHE')? : '/tmp/wapa';
    }
}
