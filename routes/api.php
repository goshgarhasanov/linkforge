<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminApiController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\LinkAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\Api\AnalyticsController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\LinkController;
use App\Controllers\Api\QrController;
use App\Controllers\Api\SettingsController;
use App\Middleware\AdminMiddleware;
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

        $group->get('/links/{code}/qr.png', [QrController::class, 'png']);
        $group->get('/links/{code}/qr.svg', [QrController::class, 'svg']);

        $group->group('', function (RouteCollectorProxy $protected): void {
            $protected->get('/auth/me', [AuthController::class, 'me']);

            $protected->post  ('/links',                    [LinkController::class, 'store']);
            $protected->get   ('/links',                    [LinkController::class, 'index']);
            $protected->get   ('/links/{code}',             [LinkController::class, 'show']);
            $protected->delete('/links/{code}',             [LinkController::class, 'destroy']);
            $protected->get   ('/links/{code}/analytics',   [AnalyticsController::class, 'show']);

            $protected->patch ('/settings/profile',         [SettingsController::class, 'updateProfile']);
            $protected->post  ('/settings/password',        [SettingsController::class, 'changePassword']);
            $protected->get   ('/settings/tokens',          [SettingsController::class, 'listTokens']);
            $protected->post  ('/settings/tokens',          [SettingsController::class, 'createToken']);
            $protected->delete('/settings/tokens/{id:[0-9]+}', [SettingsController::class, 'revokeToken']);

            $protected->group('/admin', function (RouteCollectorProxy $admin): void {
                $admin->get   ('/overview',                       [AdminApiController::class,  'overview']);
                $admin->get   ('/health',                         [AdminApiController::class,  'health']);

                $admin->get   ('/users',                          [UserAdminController::class, 'index']);
                $admin->get   ('/users/{uuid}',                   [UserAdminController::class, 'show']);
                $admin->patch ('/users/{uuid}/role',              [UserAdminController::class, 'updateRole']);
                $admin->patch ('/users/{uuid}/toggle-active',     [UserAdminController::class, 'toggleActive']);

                $admin->get   ('/links',                          [LinkAdminController::class, 'index']);
                $admin->patch ('/links/{uuid}/flag',              [LinkAdminController::class, 'flag']);
                $admin->patch ('/links/{uuid}/unflag',            [LinkAdminController::class, 'unflag']);
                $admin->patch ('/links/{uuid}/toggle-active',     [LinkAdminController::class, 'toggleActive']);
                $admin->delete('/links/{uuid}',                   [LinkAdminController::class, 'destroy']);

                $admin->get   ('/audit',                          [AuditLogController::class,  'index']);
            })->add(AdminMiddleware::class);
        })->add(AuthMiddleware::class);
    });
};
