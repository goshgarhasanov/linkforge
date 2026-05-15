<?php

declare(strict_types=1);

use App\Services\AnalyticsService;
use App\Services\ApiTokenService;
use App\Services\AuditLogger;
use App\Services\AuthService;
use App\Services\BillingService;
use App\Services\BulkLinkImporter;
use App\Services\ClickTracker;
use App\Services\EmailVerificationService;
use App\Services\LinkService;
use App\Services\NotificationService;
use App\Services\OAuthService;
use App\Services\ShortCodeGenerator;
use App\Services\TokenService;
use App\Services\TwoFactorService;
use App\Services\WebhookDispatcher;
use App\Support\Translator;
use App\Support\TwigExtension;
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

    Translator::class => static function (ContainerInterface $c): Translator {
        $config = $c->get('config')['app'];

        return new Translator(
            langPath: $config['paths']['resources'] . '/lang',
            locale: $config['locale'] ?? 'az',
        );
    },

    Twig::class => static function (ContainerInterface $c): Twig {
        $config = $c->get('config')['app'];
        $loader = new FilesystemLoader($config['paths']['resources'] . '/views');

        $twig = new Twig($loader, [
            'cache' => $config['env'] === 'production' ? $config['paths']['cache'] . '/views' : false,
            'debug' => $config['debug'],
            'auto_reload' => true,
        ]);

        $twig->getEnvironment()->addExtension(new TwigExtension($c->get(Translator::class)));

        return $twig;
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

    AuthService::class              => \DI\autowire(),
    LinkService::class              => \DI\autowire(),
    AnalyticsService::class         => \DI\autowire(),
    ApiTokenService::class          => \DI\autowire(),
    AuditLogger::class              => \DI\autowire(),
    BillingService::class           => \DI\autowire(),
    BulkLinkImporter::class         => \DI\autowire(),
    ClickTracker::class             => \DI\autowire(),
    EmailVerificationService::class => \DI\autowire(),
    NotificationService::class      => \DI\autowire(),
    OAuthService::class             => \DI\autowire(),
    TwoFactorService::class         => \DI\autowire(),
    WebhookDispatcher::class        => \DI\autowire(),
];
