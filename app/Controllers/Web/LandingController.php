<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

final class LandingController
{
    public function __construct(
        private readonly Twig $view,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->view->render($response, 'public/landing.twig', [
            'app_name' => $_ENV['APP_NAME'] ?? 'LinkForge',
            'app_url'  => rtrim($_ENV['APP_URL'] ?? '', '/'),
        ]);
    }
}
