<?php

namespace SniWapa\Lib;

use SniWapa\Lib\ConfigStorage;

/**
 * This class chooses a more or less random picture to show.
 */
class PictureSelector
{
    private $pathes;

    public function __construct(ConfigStorage $configStorage)
    {
        $this->configStorage = $configStorage;

        if ($configStorage->useNullImage()) {
            $this->pathes = [__DIR__ . '/../Assets/NullImage.jpg'];
            return;
        }

        $this->checkImageSource();
        $this->pathes = glob(getenv('IMAGE_SOURCE') . '/*');
        $this->logPath = DI::getFileCachePath() . '/pathes.log';
    }

    public function checkImageSource()
    {
        $dir = getenv('IMAGE_SOURCE');
        if (!$dir) {
            $error  = 'Parameter IMAGE_SOURCE (path to source images\' folder) not found in .env-file.';
            throw new \Exception($error);
        }
        if (!file_exists(getenv('IMAGE_SOURCE'))) {
            throw new \Exception("Please check parameter IMAGE_SOURCE. Folder {$dir} does not seem to exist.");
        }
    }

    public function chooseOne(): string
    {
        $key = rand(0, count($this->pathes) - 1);
        $path = $this->pathes[$key];

        if (count($this->pathes) > 0) {
            $this->logPath($path);
        }

        return $path;
    }

    public function chooseNext(): string
    {
        $current = self::getCurrentDisplayedWallpaper();

        if (!$current) {
            $resultingPath = $this->pathes[0];
        } else {
            $key = array_search(trim($current), $this->pathes);
            if ($key === false) {
                throw new \Exception("Pfad {$current} nicht gefunden.");
            }
            if ($key == count($this->pathes) - 1) {
                throw new \Exception("Ordner wurde komplett durchsucht");
            }
            $resultingPath = $this->pathes[$key + 1];
        }

        $this->logPath($resultingPath);
        return $resultingPath;
    }

    public function logPath($path)
    {
        file_put_contents($this->logPath, "\n" . $path, FILE_APPEND);
    }

    public static function getCurrentDisplayedWallpaper(): string
    {
        $command = 'tail -n 1 ' . DI::getFileCachePath() . '/pathes.log';
        $path = `$command`;
        return $path ? $path : '';
    }
}
