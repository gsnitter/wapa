<?php

namespace SniWapa\tests\Lib;

use SniWapa\Lib\ImagePercentage;
use PHPUnit\Framework\TestCase;
use SniWapa\Entity\Dimension;

class ImagePercentageTest extends TestCase
{
    public function setUp()
    {
        $this->service = new ImagePercentage();
    }

    public function testGetNewDim_smaller()
    {
        $this->assertSame('250x500', $this->service->getNewDim(
            new Dimension(100, 200),
            new Dimension(1000, 1000),
            50,
            50
        )->__toString());
    }

    public function testGetNewDim_tooHigh()
    {
        $this->assertSame('25x500', $this->service->getNewDim(
            new Dimension(100, 2000),
            new Dimension(1000, 1000),
            50,
            50
        )->__toString());
    }

    public function testGetNewDim_float()
    {
        $this->assertSame('150x300', $this->service->getNewDim(
            new Dimension(100, 200),
            new Dimension(300, 6000),
            50,
            50
        )->__toString());
    }

    public function testRegression_1()
    {
        $this->assertSame('405x540', $this->service->getNewDim(
            new Dimension(1920, 2560),
            new Dimension(1920, 1080),
            50,
            50
        )->__toString());
    }
}
