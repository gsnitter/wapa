<?php

namespace SniWapa\Lib;

use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;

class Filesystem extends BaseFileSystem
{

    // Unfortunately, F. Potencier refuses to wrap file_get_contents,
    // though for testing, it is very handy.
    public function getContent(string $path): string
    {
        return file_get_contents($path);
    }
}
