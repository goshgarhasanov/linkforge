<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Models\User;
use App\Support\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class LocaleController
{
    public function set(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $locale = (string) $args['locale'];

        if (! Translator::isSupported($locale)) {
            $locale = 'az';
        }

        $user = $request->getAttribute('user');
        if ($user instanceof User) {
            $user->forceFill(['locale' => $locale])->save();
        }

        $params = $request->getQueryParams();
        $redirectTo = (string) ($params['redirect'] ?? '/');

        if (! str_starts_with($redirectTo, '/')) {
            $redirectTo = '/';
        }

        return (new Response())
            ->withStatus(302)
            ->withHeader('Location', $redirectTo)
            ->withHeader(
                'Set-Cookie',
                sprintf(
                    'lf_locale=%s; Path=/; Max-Age=31536000; SameSite=Lax%s',
                    $locale,
                    ($_ENV['APP_ENV'] ?? 'local') === 'production' ? '; Secure' : '',
                ),
            );
    }
}
