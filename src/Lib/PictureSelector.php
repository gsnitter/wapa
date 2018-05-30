<?php

namespace SniWapa\Lib;

use SniWapa\Lib\ConfigStorage;

/**
 * This class chooses a more or less random picture to show.
 */
class PictureSelector
{
    private $pathes;

    /** @var ConfigStorage $configStorage */
    private $configStorage;

    /** @var Filesystem $fs */
    private $fs;

    /** @var string $logPath */
    private $logPath;

    public function __construct(ConfigStorage $configStorage, Filesystem $fs)
    {
        $this->configStorage = $configStorage;
        $this->fs = $fs;

        if ($configStorage->useNullImage()) {
            $this->pathes = [__DIR__ . '/../Assets/NullImage.jpg'];
            return;
        }

        $this->checkImageSource();
        $this->getPathes();
        $this->logPath = DI::getFileCachePath() . '/pathes.log';
    }

    private function getPathes()
    {
        $this->pathes = glob(getenv('IMAGE_SOURCE') . '/*');

        foreach ($this->pathes as $index => $path) {
            if (preg_match('@pathes.log@', $path)) {
                unset($this->pathes[$index]);
            }
        }

        return $this->pathes;
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
        if (count($this->pathes) == 1 && basename($this->pathes[0]) == 'NullImage.jpg') {
            return $this->pathes[0];
        }
        $current = self::getCurrentDisplayedWallpaper();

        if (!$current) {
            $resultingPath = $this->pathes[0];
        } else {
            $key = array_search(trim($current), $this->pathes);
            if ($key === false) {
                $text =  "Path {$current} not found.";
                if (count($this->pathes) == 0) {
                    $text .= ' No pictures found in ' . getenv('IMAGE_SOURCE') . '.';
                } else {
                    $text .= ' Searching in ' . dirname($this->pathes[0]) . '.';
                }
                throw new \Exception($text);
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
        // Wenn wir das "NULL"-Image anzeigen, loggen wir den Pfad nicht.
        if (!$this->logPath) {
            return;
        }

        $this->fs->mkdir(dirname($this->logPath));
        file_put_contents($this->logPath, "\n" . $path, FILE_APPEND);
    }

    public function deleteCurrentImage()
    {
        $currentImagePath = `tail -n 1 {$this->logPath}`;
        `head -n -1 {$this->logPath} > {$this->logPath}.bak ; mv {$this->logPath}.bak {$this->logPath}`;
        $this->fs->remove($currentImagePath);
        echo "{$currentImagePath} deleted\n";
    }

    public static function getCurrentDisplayedWallpaper(): string
    {
        if (!is_readable(DI::getFileCachePath() . '/pathes.log')) {
            return '';
        }

        $command = 'tail -n 1 ' . DI::getFileCachePath() . '/pathes.log';
        $path = `$command`;
        return $path ? $path : '';
    }
}
