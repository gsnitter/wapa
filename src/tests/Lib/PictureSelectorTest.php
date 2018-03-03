<?php

namespace SniWapa\tests\Lib;

use SniWapa\Lib\PictureSelector;
use PHPUnit\Framework\TestCase;
use SniWapa\tests\Lib\MockedFilesystem;

class PictureSelectorTest extends TestCase
{
    public function testChooseOne()
    {
        $configStorage = $this->createMock('SniWapa\Lib\ConfigStorage');
        $configStorage
            ->expects($this->once())
            ->method('useNullImage')
            ->willReturn(true);

        $selector = new PictureSelector($configStorage, new MockedFilesystem());
        $this->assertRegExp('@/Assets/NullImage.jpg@', $selector->chooseOne());
        $this->assertFileExists($selector->chooseOne());
    }
}
