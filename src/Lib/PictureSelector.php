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
        return $this->pathes[$key];
    }
}
