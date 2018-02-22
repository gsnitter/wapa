<?php

namespace SniWapa\tests\Lib;

use SniWapa\Lib;
use PHPUnit\Framework\TestCase;
use SniWapa\Lib\HardLinker;

class HardLinkerTest extends TestCase
{
    public function testGetMaxPostfix()
    {
        $hardLinker = new HardLinker();

        $this->assertSame(0, $hardLinker->getMaxPostfix([]));
        $this->assertSame(23, $hardLinker->getMaxPostfix([
            '/eins/zwei/irgendwas_23.jpg',
            '/eins/zwei/irgendwas_12.png',
        ]));
        $this->assertSame(32, $hardLinker->getMaxPostfix([
            '/eins/zwei/irgendwas_9.jpg',
            '/eins/zwei/egal',
            '/eins/zwei/irgendwas_32.png',
        ]));
    }
}
