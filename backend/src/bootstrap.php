<?php

declare(strict_types=1);

use App\Autoloader;
use App\Support\Environment;

require __DIR__ . '/Autoloader.php';

Autoloader::register(__DIR__);
Environment::load(dirname(__DIR__) . '/.env');
