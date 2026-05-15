<?php

declare(strict_types=1);

use App\Controllers\Api\AuthController;
use App\Controllers\Api\LinkController;
use App\Middleware\AuthMiddleware;
use App\Support\Http\JsonResponder;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->group('/api/v1', function (RouteCollectorProxy $group): void {
        $group->get('/health', static fn () => JsonResponder::success([
            'status'    => 'ok',
            'service'   => 'linkforge',
            'version'   => '1.0.0',
            'timestamp' => (new DateTimeImmutable())->format(DateTimeInterface::ATOM),
        ]));

        $group->post('/auth/register', [AuthController::class, 'register']);
        $group->post('/auth/login',    [AuthController::class, 'login']);

        $group->group('', function (RouteCollectorProxy $protected): void {
            $protected->get('/auth/me', [AuthController::class, 'me']);

            $protected->post  ('/links',         [LinkController::class, 'store']);
            $protected->get   ('/links',         [LinkController::class, 'index']);
            $protected->get   ('/links/{code}',  [LinkController::class, 'show']);
            $protected->delete('/links/{code}',  [LinkController::class, 'destroy']);
        })->add(AuthMiddleware::class);
    });
};
