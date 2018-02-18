<?php

namespace SniWapa\tests\Lib;

use SniWapa\Lib;
use PHPUnit\Framework\TestCase;
use SniWapa\Lib\ConfigStorage;
use SniWapa\Lib\Filesystem;
use SniWapa\tests\Lib\MockedFilesystem;
use SniWapa\Lib\DI;

class ConfigStorageTest extends TestCase
{

    public function setUp()
    {
        $this->fs = new MockedFilesystem();
        $this->configStorage = new ConfigStorage($this->fs);
    }

    public function testSetX()
    {
        $this->assertSame(50, $this->configStorage->getMaxX());
        $this->assertFalse($this->configStorage->hasChanged());

        $this->configStorage->setMaxX(75);
        $this->assertSame(75, $this->configStorage->getMaxX());
        $this->assertTrue($this->configStorage->hasChanged());
    }

    public function testSetY()
    {
        $this->assertSame(100, $this->configStorage->getMaxY());
        $this->assertFalse($this->configStorage->hasChanged());

        $this->configStorage->setMaxY(75);
        $this->assertSame(75, $this->configStorage->getMaxY());
        $this->assertTrue($this->configStorage->hasChanged());
    }

    public function testSetBackgroundColor()
    {
        $this->assertRegExp('@^#\w{6}$@', $this->configStorage->getBackgroundColor());
        $this->assertFalse($this->configStorage->hasChanged());

        $this->configStorage->setBackgroundColor('#0000FF');
        $this->assertSame('#0000FF', $this->configStorage->getBackgroundColor());
        $this->assertTrue($this->configStorage->hasChanged());
    }

    public function testGetArrayRepresentation()
    {
        $array = $this->configStorage->getArrayRepresentation();
        $this->assertSame(50, $array['maxX']);
        $this->assertSame(100, $array['maxY']);
        $this->assertRegExp('@^#\w{6}$@', $array['backgroundColor']);
    }

    public function testSaveChanges_unchanged()
    {
        $this->assertSame(50, $this->configStorage->getMaxX());
        $this->configStorage->setMaxX(50);

        $this->assertFalse($this->configStorage->saveChanges());
    }

    public function testSaveChanges_changed()
    {
        $this->assertSame(50, $this->configStorage->getMaxX());
        $this->configStorage->setMaxX(70);

        $this->assertTrue($this->configStorage->saveChanges());
        $content = $this->fs->getContent(DI::getFileCachePath());
        $this->assertContains('"maxX":70', $content);
        $this->assertFalse($this->configStorage->hasChanged());
    }

    public function testGetRGBArray()
    {
        $this->configStorage->setBackgroundColor('#112233');
        $rgbArray = $this->configStorage->getRGBArray();
        $this->assertSame(['11', '22', '33'], $rgbArray);
    }

    public function testGetBackgroundRGB()
    {
        $rgbArray = $this->configStorage->getBackgroundRGB();
        $this->assertSame('rgb(77,77,77)', $rgbArray);

        $this->configStorage->setBackgroundColor('#112233');
        $rgbArray = $this->configStorage->getBackgroundRGB();
        $this->assertSame('rgb(11,22,33)', $rgbArray);
    }

    public function testLoadConfig()
    {
        $this->configStorage
            ->setBackgroundColor('#224466')
            ->setMaxX(20)
            ->setMaxY(30);
        $this->configStorage->saveChanges();
        $this->configStorage
            ->setBackgroundColor('#000000')
            ->setMaxX(10)
            ->setMaxY(90);
        
        $this->assertSame(10, $this->configStorage->getMaxX());

        $this->configStorage->loadConfig();

        $this->assertSame(20, $this->configStorage->getMaxX());
        $this->assertSame(30, $this->configStorage->getMaxY());
        $this->assertSame('#224466', $this->configStorage->getBackgroundColor());
    }
}
