<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use App\Support\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

final class LocaleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Translator $translator,
        private readonly Twig $view,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $this->resolveLocale($request);
        $this->translator->setLocale($locale);

        $this->view->getEnvironment()->addGlobal('locale', $locale);
        $this->view->getEnvironment()->addGlobal('supported_locales', Translator::supported());

        $response = $handler->handle($request);

        return $response->withHeader('Content-Language', $locale);
    }

    private function resolveLocale(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        if (! empty($params['lang']) && Translator::isSupported((string) $params['lang'])) {
            return (string) $params['lang'];
        }

        $cookies = $request->getCookieParams();
        if (! empty($cookies['lf_locale']) && Translator::isSupported((string) $cookies['lf_locale'])) {
            return (string) $cookies['lf_locale'];
        }

        $user = $request->getAttribute('user');
        if ($user instanceof User && Translator::isSupported($user->locale)) {
            return $user->locale;
        }

        $accept = $request->getHeaderLine('Accept-Language');
        if ($accept !== '') {
            foreach (explode(',', $accept) as $part) {
                $lang = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
                if (Translator::isSupported($lang)) {
                    return $lang;
                }
            }
        }

        return $_ENV['APP_LOCALE'] ?? 'az';
    }
}
