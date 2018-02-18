<?php

namespace SniWapa\tests\Lib;

use SniWapa\Lib\Filesystem;

class MockedFilesystem extends Filesystem
{
    private $fileContent = [];

    // Symfony did not yet include type hints.
    public function dumpFile($path, $content)
    {
        $this->fileContent[$path] = $content;
    }

    public function getContent(string $path): string
    {
        if (!isset($this->fileContent[$path])) {
            throw new \InvalidArgumentException("MockedFilesystem: path {$path} not initalized");
        }

        return $this->fileContent[$path];
    }

    public function exists($file)
    {
        return isset($this->fileContent[$file]);
    }
}
