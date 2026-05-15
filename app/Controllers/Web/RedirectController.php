<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Models\Link;
use App\Services\ClickTracker;
use App\Services\LinkService;
use App\Support\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Slim\Views\Twig;

final class RedirectController
{
    public function __construct(
        private readonly LinkService $links,
        private readonly ClickTracker $tracker,
        private readonly Twig $view,
    ) {
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $code = (string) $args['code'];

        try {
            $link = $this->links->findByCode($code);
        } catch (HttpException) {
            return $this->renderError($request, 404, 'Bu kodla heç bir link tapılmadı.');
        }

        if (! $link->isAccessible()) {
            return $this->renderError(
                $request,
                410,
                $this->inaccessibleReason($link),
            );
        }

        if ($link->requiresPassword()) {
            return $this->renderPasswordPrompt($request, $link);
        }

        $this->tracker->track($link, $request);

        return $this->buildRedirect($request, $link);
    }

    public function unlock(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $code = (string) $args['code'];

        try {
            $link = $this->links->findByCode($code);
        } catch (HttpException) {
            return $this->renderError($request, 404, 'Bu kodla heç bir link tapılmadı.');
        }

        $password = (string) ($request->getParsedBody()['password'] ?? '');

        if (! $link->verifyPassword($password)) {
            return $this->view->render(
                new Response(401),
                'public/password.twig',
                ['link' => $link, 'error' => 'Şifrə yanlışdır. Yenidən cəhd edin.'],
            );
        }

        $this->tracker->track($link, $request);

        return $this->buildRedirect($request, $link);
    }

    private function buildRedirect(ServerRequestInterface $request, Link $link): ResponseInterface
    {
        $target = $this->resolveTarget($request, $link);

        return (new Response())
            ->withStatus(302)
            ->withHeader('Location', $target)
            ->withHeader('Cache-Control', 'no-store, must-revalidate');
    }

    private function resolveTarget(ServerRequestInterface $request, Link $link): string
    {
        $ua = strtolower($request->getHeaderLine('User-Agent'));

        if ($link->ios_deep_link && (str_contains($ua, 'iphone') || str_contains($ua, 'ipad'))) {
            return $link->ios_deep_link;
        }

        if ($link->android_deep_link && str_contains($ua, 'android')) {
            return $link->android_deep_link;
        }

        return $link->original_url;
    }

    private function renderPasswordPrompt(ServerRequestInterface $request, Link $link): ResponseInterface
    {
        return $this->view->render(
            new Response(),
            'public/password.twig',
            ['link' => $link, 'error' => null],
        );
    }

    private function renderError(ServerRequestInterface $request, int $status, string $message): ResponseInterface
    {
        return $this->view->render(
            new Response($status),
            'errors/' . $status . '.twig',
            ['message' => $message],
        );
    }

    private function inaccessibleReason(Link $link): string
    {
        return match (true) {
            ! $link->is_active        => 'Bu link deaktiv edilib.',
            $link->is_flagged         => 'Bu link təhlükəsizlik səbəbindən bloklanıb.',
            $link->isExpired()        => 'Bu linkin istifadə müddəti başa çatıb.',
            $link->hasReachedClickLimit() => 'Bu link maksimum klik sayına çatıb.',
            default                   => 'Bu link hazırda əlçatan deyil.',
        };
    }
}
