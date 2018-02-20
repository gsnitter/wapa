<?php

namespace SniWapa\Lib;

use SniWapa\Entity\Dimension;
use SniWapa\Lib\Logger;
use SniWapa\Lib\Screen;

class ImageCreator
{
    /** @var ConfigStorage */
    private $config;
    
    /** @var ImagePercentage */
    private $imagePercentage;

    public function __construct(ConfigStorage $config, ImagePercentage $imagePercentage)
    {
        $this->config = $config;
        $this->imagePercentage = $imagePercentage;
    }

    public function create(string $imageIn): string
    {
        if (!is_readable($imageIn)) {
            throw new \Exception("Image $imageIn not found");
        }

        $this->magick($imageIn);

        return $this->getBuildPath() . '/final.png';
    }

    private function getBuildPath(): string
    {
        return getenv('BUILD_FOLDER') ? : DI::getProjectPath() . '/buildFolder';
    }

    private function log($msg, $overwrite = false)
    {
        $command  = 'echo "' . microtime(true);
        $command .= $msg ? ' ' . $msg . '"' : '"';
        $command .= $overwrite ? ' > ' : ' >> ';
        $command .= $this->getBuildPath() . "/create.log";
        exec($command);
    }

    private function createGradientIfNotExists()
    {
        $buildPath = $this->getBuildPath();
        $displayString = Screen::getDisplayString();

        $this->log('Start', true);
        if (!is_readable($buildPath . '/general_mask.png')) {
            $this->log('Creating mask');
            $command =
<<<MAGICK_COMMAND
            cd $buildPath;
            $displayString convert -size 500x500 xc: -channel G -fx '(1-abs(2*i/w-1)^1.5)*(1-abs(2*j/h-1)^3.2)' -separate gr.png
            $displayString convert gr.png -alpha copy -channel A -negate general_mask.png
            rm gr.png
MAGICK_COMMAND;
            exec($command);
        }
    }

    private function magick(string $imageIn)
    {
        $escapedImageIn = str_replace(' ', '\ ', $imageIn);
        $picDim = Dimension::createByImage($escapedImageIn);
        $resDim = Dimension::createByResolution($escapedImageIn);
        $newDim = $this->imagePercentage->getNewDim(
            $picDim,
            $resDim,
            $this->config->getMaxX(),
            $this->config->getMaxY()
        );

        $this->createGradientIfNotExists();
        $buildPath = $this->getBuildPath();
        $backgroundRGB = $this->config->getBackgroundRGB();
        $displayString = Screen::getDisplayString();

        $this->log('Creating image');
        $command = 
<<<MAGICK_COMMAND
            cd $buildPath;
            # Verkleinern auf halbe Bildschirmbreite mit korrekter Aspect Ratio
            {$displayString} convert {$escapedImageIn} -auto-orient -resize {$newDim} out.png;

            # Ein Gradient als Rahmen, könnte man eigentlich einmalig berechnen und dann passend skalieren
            # convert out.png xc: -channel G -fx '(1-abs(2*i/w-1)^1.5)*(1-abs(2*j/h-1)^3.2)' -separate gr.png
            # convert gr.png -alpha copy -channel A -negate mask.png

            # Obige zwei Zeilen sind rechenintensiv, besser mask einmal berechnen, dann resizen mit
            {$displayString} convert general_mask.png -resize '{$newDim->getWidth()}!x{$newDim->getHeight()}!' mask.png

            {$displayString} convert -size {$newDim} xc:'{$backgroundRGB}' small_background.png
            {$displayString} convert small_background.png out.png mask.png -composite result.png

            # Jetzt ein großes Bild kreiern
            {$displayString} convert -size {$resDim} xc:'{$backgroundRGB}' big_background.png

            # Schließlich zusammenfügen
            {$displayString} convert -alpha off -gravity southeast big_background.png result.png -composite final.png
MAGICK_COMMAND;
        $this->log($command);
        exec($command);
        $this->log('Done');
    }
}
