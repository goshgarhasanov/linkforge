<?php

declare(strict_types=1);

namespace App\Support;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\App;
use Slim\Factory\AppFactory;

final class Application
{
    private function __construct(private readonly App $slim)
    {
    }

    public static function create(string $basePath): self
    {
        self::loadEnvironment($basePath);
        self::configureErrorReporting();

        $container = self::buildContainer($basePath);
        AppFactory::setContainer($container);

        $slim = AppFactory::create();
        self::registerDatabase($basePath);
        self::registerMiddleware($slim);
        self::registerRoutes($slim, $basePath);

        return new self($slim);
    }

    public function run(): void
    {
        $this->slim->run();
    }

    private static function loadEnvironment(string $basePath): void
    {
        if (file_exists($basePath . '/.env')) {
            Dotenv::createImmutable($basePath)->load();
        }

        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Baku');
    }

    private static function configureErrorReporting(): void
    {
        $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
    }

    private static function buildContainer(string $basePath): \Psr\Container\ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);

        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            $builder->enableCompilation($basePath . '/storage/cache');
        }

        $builder->addDefinitions(require $basePath . '/app/Support/dependencies.php');

        return $builder->build();
    }

    private static function registerDatabase(string $basePath): void
    {
        $config = require $basePath . '/config/database.php';
        $default = $config['default'];

        $capsule = new Capsule();
        $capsule->addConnection($config['connections'][$default]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    private static function registerMiddleware(App $slim): void
    {
        $slim->addBodyParsingMiddleware();
        $slim->addRoutingMiddleware();

        $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $slim->addErrorMiddleware($debug, true, true);
    }

    private static function registerRoutes(App $slim, string $basePath): void
    {
        (require $basePath . '/routes/web.php')($slim);
        (require $basePath . '/routes/api.php')($slim);
    }
}
