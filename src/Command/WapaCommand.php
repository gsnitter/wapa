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
use SniWapa\Lib\ConstBackgroundDisplayer;

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
            ->addOption('cron', null, INPUTOPTION::VALUE_NONE,
<<<HELP
Shows next wallpapers if wapa on is set.
HELP
            )
            ->addOption('on', null, INPUTOPTION::VALUE_NONE,
<<<HELP
Cancel the effect of the "off"-option.
HELP
            )
            ->addOption('off', null, INPUTOPTION::VALUE_NONE,
<<<HELP
Shows uniform background. The "cron"-option does not show the next image any more.
HELP
            )
            ->addOption('toggle', 't', INPUTOPTION::VALUE_NONE,
<<<HELP
Switches "on" to "of" and vice versa.
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

        if ($input->getOption('cron')) {
            $this->cron();
        }

        if ($hasChanged) {
            if ($this->getConfigStorage()->isCronActive()) {
                $this->container
                    ->get(ImageCreator::class)
                    ->clearBuildFolder();

                $this->forward();
            } else {
                $this->showConstantBackground();
            }
        }

        if ($input->getOption('forward')) {
            $this->forward();
        }

        if ($input->getOption('off')) {
            $this->setCronOff();
        }

        if ($input->getOption('on')) {
            $this->setCronOn();
        }

        if ($input->getOption('toggle')) {
            $this->toggleCron();
        }

        if ($path = $input->getOption('hard-link-current')) {
            $this->hardLinkCurrentWallpaperTo($path);
        }
    }

    private function toggleCron()
    {
        if ($this->getConfigStorage()->isCronActive()) {
            $this->setCronOff();
        } else {
            $this->setCronOn();
        }
    }

    private function setCronOff()
    {
        $color = $this->getConfigStorage()
            ->setCronActive(false)
            ->saveChanges(true);

        $this->showConstantBackground();
    }

    private function showConstantBackground()
    {
        $this->container
            ->get(ConstBackgroundDisplayer::class)
            ->showBackground($this->getConfigStorage()->getBackgroundColor());
    }

    private function setCronOn()
    {
        $color = $this->getConfigStorage()
            ->setCronActive(true)
            ->saveChanges(true);
        $this->forward();
    }

    private function cron()
    {
        if ($this->getConfigStorage()->isCronActive()) {
            $this->forward();
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
        $backgroundColor = preg_replace('@^=@', '', $backgroundColor);
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
            ->chooseOne();
            // ->chooseNext();

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
