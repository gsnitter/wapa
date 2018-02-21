<?php

namespace SniWapa\Lib;

class ConfigStorage
{
    private $maxX = 50;
    private $maxY = 100;
    private $backgroundColor = '#777777';
    private $hasChanged = false;
    private $fs;

    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
        $this->loadConfig();
    }

    public function setMaxX(int $maxX): ConfigStorage
    {
        if ($maxX == $this->maxX) {
            return $this;
        }

        $this->hasChanged = true;
        $this->maxX = $maxX;
        return $this;
    }

    public function getMaxX(): int
    {
        return $this->maxX;
    }

    public function setMaxY(int $maxY): ConfigStorage
    {
        if ($maxY == $this->maxY) {
            return $this;
        }

        $this->hasChanged = true;
        $this->maxY = $maxY;
        return $this;
    }

    public function getMaxY(): int
    {
        return $this->maxY;
    }

    public function setBackgroundColor(string $backgroundColor): ConfigStorage
    {
        if ($backgroundColor == $this->backgroundColor) {
            return $this;
        }

        $this->hasChanged = true;
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }

    public function getArrayRepresentation()
    {
        return [
            'maxX' => $this->getMaxX(),
            'maxY' => $this->getMaxY(),
            'backgroundColor' => $this->getBackgroundColor(),
        ];
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * Returns something like "rgb(00,ff,00)".
     */
    public function getBackgroundRGB(): string
    {
        list($red, $green, $blue) = $this->getRGBArray();
        return "rgb({$red},{$green},{$blue})";
    }

    public function getRGBArray(): array
    {
        preg_match('@#(\w{2})(\w{2})(\w{2})@', $this->backgroundColor, $matches);

        if (!count($matches) == 4) {
            throw new \Exception("BackgroundColor {$this->backgroundColor} not parseable"); 
        }

        array_shift($matches);
        return $matches;
    }

    public function saveChanges(): bool
    {
        if (!$this->hasChanged()) {
            return false;
        }

        $this->fs->dumpFile(
            DI::getConfigPath(),
            json_encode($this->getArrayRepresentation())
        );
        $this->hasChanged = false;
        return true;
    }

    public function loadConfig(): bool
    {
        $path = DI::getConfigPath();

        if (!$this->fs->exists($path)) {
            return false;
        }

        $arrayRep = json_decode($this->fs->getContent($path), true);
        foreach ($arrayRep as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->$setter($value);
        }
        return true;
    }

    public function useNullImage(): bool
    {
        return ($this->getMaxX() == 0 || $this->getMaxY() == 0);
    }
}
