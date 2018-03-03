<?php

namespace SniWapa\Command;
require_once __DIR__ . '/../bootstrap.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use SniWapa\Lib\DI;
use SniWapa\Lib\ConfigStorage;
use SniWapa\Lib\ImageCreator;
use SniWapa\Lib\Wallpaper;
use SniWapa\Lib\PictureSelector;
use SniWapa\Lib\Logger;
use SniWapa\Lib\HardLinker;

class WapaCommand extends Command
{
    private $output;
    private $input;
    private $container;

    protected function configure()
    {
        $this
            ->setName('wapa')
            ->setDescription('Creates and shows wallpaper backgrounds')
            ->addOption('max-x', 'x', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Sets the maximum height of the wallpapers in percent of screen size.
HELP
            )
            ->addOption('max-y', 'y', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Sets the maximum width of the wallpapers in percent of screen size.
HELP
            )
            ->addOption('background-color', 'b', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Sets the background color of the wallpapers, given in HEX-Format like "#ff0000".
HELP
            )
            ->addOption('create', 'c', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Create a wallpaper. Expects a path to an image.
HELP
            )
            ->addOption('show', 's', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Creates and displays an image as a wallpaper.
HELP
            )
            ->addOption('forward', 'f', INPUTOPTION::VALUE_NONE,
<<<HELP
Show next random wallpaper.
HELP
            )
            ->addOption('Forward', 'F', INPUTOPTION::VALUE_NONE,
<<<HELP
Returns a path to a random wallpaper.
HELP
            )
            ->addOption('hard-link-current', 'H', INPUTOPTION::VALUE_REQUIRED,
<<<HELP
Usage: wapa --hard-link-current /some/target/path/image_prefix
Hard links the current original image to something like /some/target/path/image_prefix_12.jpg,
keeping the original file extension and giving it a unique numeric postfix.
HELP
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;
        $this->container = DI::getContainer();

        if ($maxX = $input->getOption('max-x')) {
            $this->setMaxX($maxX);
        }
        if ($maxY = $input->getOption('max-y')) {
            $this->setMaxY($maxY);
        }
        if ($backgroundColor = $input->getOption('background-color')) {
            $this->setBackgroundColor($backgroundColor);
        }

        $hasChanged = $this->getConfigStorage()->saveChanges();

        if ($imageIn = $input->getOption('create')) {
            $imageOut = $this->createImage($imageIn);
            die($imageOut);
        }

        if ($imageIn = $input->getOption('show')) {
            $this->show($imageIn);
        }

        if ($hasChanged || $input->getOption('forward')) {
            $this->forward();
        }

        if ($input->getOption('Forward')) {
            $this->showRandomWallpaperPath();
        }

        if ($path = $input->getOption('hard-link-current')) {
            $this->hardLinkCurrentWallpaperTo($path);
        }
    }

    private function createImage(string $imageIn): string
    {
        return $this->container
            ->get(ImageCreator::class)
            ->create($imageIn);
    }

    private function setMaxX(string $maxX)
    {
        $this->getConfigStorage()
            ->setMaxX(intval($maxX));
    }

    private function setMaxY(string $maxY)
    {
        $this->getConfigStorage()
            ->setMaxY(intval($maxY));
    }

    private function setBackgroundColor(string $backgroundColor)
    {
        $this->getConfigStorage()
            ->setBackgroundColor($backgroundColor);
    }

    private function getConfigStorage(): ConfigStorage
    {
        return $this->container
            ->get(ConfigStorage::class);
    }

    private function show(string $imageIn)
    {
        return $this->container
            ->get(Wallpaper::class)
            ->show($imageIn);
    }

    private function forward()
    {
        $path = $this->container
            ->get(PictureSelector::class)
            // ->chooseOne();
            ->chooseNext();

        $this->show($path);
    }

    private function showRandomWallpaperPath()
    {
        $imageIn = $this->container
            ->get(PictureSelector::class)
            ->chooseOne();

        $path = $this->container
            ->get(Wallpaper::class)
            ->prepare($imageIn);

        $this->output->writeln($path);
    }

    private function hardLinkCurrentWallpaperTo(string $path)
    {
        $this->container
            ->get(HardLinker::class)
            ->hardLinkCurrentWallpaperTo($path);
    }
}
