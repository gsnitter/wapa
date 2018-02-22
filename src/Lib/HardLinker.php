<?php

namespace SniWapa\Lib;

use SniWapa\Lib\Filesystem;

class HardLinker
{

    /** @var Filesystem $fs */
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @param string - E.g. /some/path/urlaub_malta_10_2004
     */
    public function hardLinkCurrentWallpaperTo(string $path)
    {
        $dir = $this->dirname($path);
        $nextPostfix = $this->getMaxPostfix(glob($dir . '/*')) + 1;
        $currentPath = PictureSelector::getCurrentDisplayedWallpaper();
        $extension = pathinfo($currentPath)['extension'] ?? null;
        if (!$extension) {
            throw new \Exception("Cannot extract extension of current wallpaper {$currentPath}.");
        }

        $target = "{$path}_{$nextPostfix}." . $extension;
        link($currentPath, $target);
    }

    public function getMaxPostfix(array $pathes): int
    {
        return array_reduce($pathes, function($max, $file) {
            preg_match('@.*_(\d+)\.(\w+)$@', $file, $matches);
            if (!count($matches) == 3) {
                return $max;
            }

            return max(intval($matches[1]), $max);
        }, 0);
    }

    private function dirname(string $path): string
    {
        $dir = dirname($path);

        if (!$this->fs->exists($dir)) {
            throw new \Exception("Folder {$dir} not found.");
        }

        return $dir;
    }
}
