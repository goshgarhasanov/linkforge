<?php

declare(strict_types=1);

use App\Services\AuthService;
use App\Services\LinkService;
use App\Services\ShortCodeGenerator;
use App\Services\TokenService;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Predis\Client as RedisClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;
use Twig\Loader\FilesystemLoader;

return [
    'config' => static function (): array {
        return [
            'app'       => require dirname(__DIR__, 2) . '/config/app.php',
            'database'  => require dirname(__DIR__, 2) . '/config/database.php',
            'jwt'       => require dirname(__DIR__, 2) . '/config/jwt.php',
            'logging'   => require dirname(__DIR__, 2) . '/config/logging.php',
            'ratelimit' => require dirname(__DIR__, 2) . '/config/ratelimit.php',
        ];
    },

    LoggerInterface::class => static function (ContainerInterface $c): LoggerInterface {
        $config = $c->get('config')['logging'];
        $logger = new Logger($config['channel']);
        $logger->pushHandler(new RotatingFileHandler($config['path'], $config['days'], $config['level']));

        return $logger;
    },

    RedisClient::class => static function (ContainerInterface $c): RedisClient {
        $config = $c->get('config')['database']['redis'];

        return new RedisClient([
            'scheme'   => 'tcp',
            'host'     => $config['host'],
            'port'     => $config['port'],
            'password' => $config['password'],
            'database' => $config['database'],
        ]);
    },

    Twig::class => static function (ContainerInterface $c): Twig {
        $config = $c->get('config')['app'];
        $loader = new FilesystemLoader($config['paths']['resources'] . '/views');

        return new Twig($loader, [
            'cache' => $config['env'] === 'production' ? $config['paths']['cache'] . '/views' : false,
            'debug' => $config['debug'],
            'auto_reload' => true,
        ]);
    },

    ShortCodeGenerator::class => static function (ContainerInterface $c): ShortCodeGenerator {
        $config = $c->get('config')['app']['shortcode'];

        return new ShortCodeGenerator(
            length: $config['length'],
            alphabet: $config['alphabet'],
            reserved: $config['reserved'],
        );
    },

    TokenService::class => static function (ContainerInterface $c): TokenService {
        $config = $c->get('config')['jwt'];

        return new TokenService(
            secret: $config['secret'],
            algorithm: $config['algorithm'],
            ttl: $config['ttl'],
            issuer: $config['issuer'],
            audience: $config['audience'],
        );
    },

    AuthService::class => \DI\autowire(),
    LinkService::class => \DI\autowire(),
];
