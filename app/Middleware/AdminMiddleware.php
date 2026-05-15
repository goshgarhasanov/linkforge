<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Views\Twig;

final class AdminMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Twig $view,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var User|null $user */
        $user = $request->getAttribute('user');

        if (! $user instanceof User || ! $user->role->canAccessAdmin()) {
            if ($this->wantsJson($request)) {
                return JsonResponder::error('Bu səhifəyə yalnız administratorlar daxil ola bilər.', 403);
            }

            return $this->view->render(new Response(403), 'errors/403.twig', [
                'message' => 'Bu səhifəyə yalnız administratorlar daxil ola bilər.',
            ]);
        }

        return $handler->handle($request);
    }

    private function wantsJson(ServerRequestInterface $request): bool
    {
        return str_contains($request->getHeaderLine('Accept'), 'json')
            || str_starts_with($request->getUri()->getPath(), '/api/');
    }
}
