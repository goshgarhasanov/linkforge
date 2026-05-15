<?php

declare(strict_types=1);

use App\Support\Application;

define('LINKFORGE_START', microtime(true));

require dirname(__DIR__) . '/vendor/autoload.php';

$app = Application::create(dirname(__DIR__));
$app->run();
