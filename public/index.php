<?php

declare(strict_types=1);

use App\Bootstrap\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

Bootstrap::createApp()->run();
