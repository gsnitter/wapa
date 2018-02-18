<?php

namespace SniWapa;

use Symfony\Component\Dotenv\Dotenv;

use SniWapa\Lib\DI;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new DotEnv();
$dotenv->load(DI::getProjectPath() . '/.env');
