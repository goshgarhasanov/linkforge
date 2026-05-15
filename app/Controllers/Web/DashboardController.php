<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

final class DashboardController
{
    public function __construct(
        private readonly Twig $view,
    ) {
    }

    public function overview(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'dashboard/overview.twig', [
            'app_url' => $this->appHost(),
        ]);
    }

    public function links(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'dashboard/links.twig', [
            'app_url' => $this->appHost(),
        ]);
    }

    public function linkDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->view->render($response, 'dashboard/link_detail.twig', [
            'short_code' => $args['code'],
            'app_url'    => $this->appHost(),
        ]);
    }

    public function settings(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'dashboard/settings.twig');
    }

    private function appHost(): string
    {
        return parse_url(rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/'), PHP_URL_HOST) ?: 'localhost';
    }
}
