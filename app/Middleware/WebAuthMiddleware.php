<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class WebAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['lf_token'] ?? null;

        if ($token === null) {
            $header = $request->getHeaderLine('Authorization');
            if (str_starts_with($header, 'Bearer ')) {
                $token = trim(substr($header, 7));
            }
        }

        if ($token === null || $token === '') {
            return $handler->handle($request);
        }

        try {
            $user = $this->auth->authenticate($token);
            $request = $request->withAttribute('user', $user);
        } catch (HttpException) {
        }

        return $handler->handle($request);
    }
}
