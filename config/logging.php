<?php

declare(strict_types=1);

use Monolog\Level;

return [
    'channel' => $_ENV['LOG_CHANNEL'] ?? 'app',
    'level'   => Level::fromName(ucfirst($_ENV['LOG_LEVEL'] ?? 'debug')),
    'path'    => dirname(__DIR__) . '/storage/logs/app.log',
    'days'    => 14,
];
