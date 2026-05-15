<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Services\EmailVerificationService;
use App\Support\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Slim\Views\Twig;

final class EmailVerificationController
{
    public function __construct(
        private readonly EmailVerificationService $service,
        private readonly Twig $view,
    ) {
    }

    public function verify(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = (string) ($request->getQueryParams()['token'] ?? '');

        if ($token === '') {
            return $this->view->render(new Response(400), 'public/email_verified.twig', [
                'success' => false,
                'message' => 'Token verilməyib.',
            ]);
        }

        try {
            $user = $this->service->verify($token);
        } catch (HttpException $e) {
            return $this->view->render(new Response($e->getCode()), 'public/email_verified.twig', [
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $this->view->render($response, 'public/email_verified.twig', [
            'success' => true,
            'email'   => $user->email,
        ]);
    }
}
