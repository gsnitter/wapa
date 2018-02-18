<?php

namespace SniWapa\tests\Lib;

use PHPUnit\Framework\TestCase;
use SniWapa\Entity\Dimension;

class DimensionTest extends TestCase
{
    public function testGetWidth_Resolution()
    {
        $dim = Dimension::createByResolution();
        $this->assertGreaterThan(100, $dim->getWidth());
        $this->assertLessThan(10000, $dim->getWidth());
    }

    public function testGetHeight_Resolution()
    {
        $dim = Dimension::createByResolution();
        $this->assertGreaterThan(100, $dim->getHeight());
        $this->assertLessThan(10000, $dim->getHeight());
    }

    public function testGetWidth_Image()
    {
        $dim = Dimension::createByImage(__DIR__ . '/../Image/Tux.png');
        $this->assertSame(265, $dim->getWidth());
        $this->assertSame(314, $dim->getHeight());
    }
}
