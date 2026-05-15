<?php

declare(strict_types=1);

return [
    'name'     => $_ENV['APP_NAME']     ?? 'LinkForge',
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/'),
    'key'      => $_ENV['APP_KEY']      ?? '',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Baku',
    'locale'   => $_ENV['APP_LOCALE']   ?? 'az',

    'shortcode' => [
        'length'   => (int) ($_ENV['SHORTCODE_LENGTH'] ?? 7),
        'alphabet' => $_ENV['SHORTCODE_ALPHABET']
            ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'reserved' => array_map(
            'trim',
            explode(',', $_ENV['RESERVED_ALIASES'] ?? 'admin,api,dashboard')
        ),
    ],

    'paths' => [
        'root'      => dirname(__DIR__),
        'public'    => dirname(__DIR__) . '/public',
        'resources' => dirname(__DIR__) . '/resources',
        'storage'   => dirname(__DIR__) . '/storage',
        'logs'      => dirname(__DIR__) . '/storage/logs',
        'cache'     => dirname(__DIR__) . '/storage/cache',
    ],
];
