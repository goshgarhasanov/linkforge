<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');

        if (! str_starts_with($header, 'Bearer ')) {
            return JsonResponder::error('Avtorizasiya başlığı tələb olunur.', 401);
        }

        $token = trim(substr($header, 7));

        try {
            $user = $this->auth->authenticate($token);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode() ?: 401);
        }

        return $handler->handle($request->withAttribute('user', $user));
    }
}
