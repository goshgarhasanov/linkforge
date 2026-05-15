<?php

declare(strict_types=1);

use App\Controllers\Web\AuthPageController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\LandingController;
use App\Controllers\Web\RedirectController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\TwigMiddleware;

return static function (App $app): void {
    $container = $app->getContainer();
    $twig = $container->get(\Slim\Views\Twig::class);
    $app->add(TwigMiddleware::create($app, $twig));

    $app->get('/',         [LandingController::class,  'index'])->setName('landing');
    $app->get('/login',    [AuthPageController::class, 'login']);
    $app->get('/register', [AuthPageController::class, 'register']);

    $app->group('/dashboard', function (RouteCollectorProxy $group): void {
        $group->get('',                [DashboardController::class, 'overview']);
        $group->get('/links',          [DashboardController::class, 'links']);
        $group->get('/links/{code}',   [DashboardController::class, 'linkDetail']);
        $group->get('/settings',       [DashboardController::class, 'settings']);
    });

    $app->get('/docs', static function ($request, $response) use ($twig) {
        return $twig->render($response, 'public/docs.twig');
    });

    $app->get('/openapi.yaml', static function ($request, $response) {
        $response->getBody()->write(file_get_contents(dirname(__DIR__) . '/public/openapi.yaml') ?: '');
        return $response->withHeader('Content-Type', 'application/yaml');
    });

    $app->get ('/{code:[A-Za-z0-9_-]{3,32}}',          [RedirectController::class, 'handle']);
    $app->post('/{code:[A-Za-z0-9_-]{3,32}}/unlock',   [RedirectController::class, 'unlock']);
};
