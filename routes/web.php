<?php

declare(strict_types=1);

use App\Controllers\Web\LandingController;
use App\Controllers\Web\RedirectController;
use Slim\App;
use Slim\Views\TwigMiddleware;

return static function (App $app): void {
    $container = $app->getContainer();
    $twig = $container->get(\Slim\Views\Twig::class);
    $app->add(TwigMiddleware::create($app, $twig));

    $app->get('/', [LandingController::class, 'index'])->setName('landing');

    $app->get ('/{code:[A-Za-z0-9_-]{3,32}}',          [RedirectController::class, 'handle']);
    $app->post('/{code:[A-Za-z0-9_-]{3,32}}/unlock',   [RedirectController::class, 'unlock']);
};
