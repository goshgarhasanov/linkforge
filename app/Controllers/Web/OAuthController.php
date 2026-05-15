<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Services\OAuthService;
use App\Support\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class OAuthController
{
    public function __construct(
        private readonly OAuthService $oauth,
    ) {
    }

    public function redirect(ServerRequestInterface $request, array $args): ResponseInterface
    {
        try {
            $provider = $this->oauth->provider($args['provider'], $this->callbackUrl($args['provider']));
        } catch (HttpException $e) {
            return $this->redirectWithError($e->getMessage());
        }

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => $args['provider'] === 'google'
                ? ['openid', 'email', 'profile']
                : ['user:email'],
        ]);

        $state = $provider->getState();

        $response = (new Response())->withStatus(302)->withHeader('Location', $authUrl);

        return $response->withHeader(
            'Set-Cookie',
            'oauth_state=' . urlencode($state) . '; Path=/; HttpOnly; SameSite=Lax; Max-Age=600',
        );
    }

    public function callback(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $providerName = (string) $args['provider'];
        $params = $request->getQueryParams();
        $cookies = $request->getCookieParams();

        $expectedState = $cookies['oauth_state'] ?? null;
        $receivedState = $params['state'] ?? null;

        if ($expectedState === null || $receivedState !== $expectedState) {
            return $this->redirectWithError('OAuth state etibarsızdır.');
        }

        if (empty($params['code'])) {
            return $this->redirectWithError('OAuth kodu alınmadı.');
        }

        try {
            $provider = $this->oauth->provider($providerName, $this->callbackUrl($providerName));
            $tokenObj = $provider->getAccessToken('authorization_code', ['code' => $params['code']]);
            $owner = $provider->getResourceOwner($tokenObj);

            $profile = $this->normalizeProfile($providerName, $owner->toArray(), $tokenObj->getToken());

            $tokens = $this->oauth->loginOrRegister($providerName, $profile, [
                'access_token'  => $tokenObj->getToken(),
                'refresh_token' => $tokenObj->getRefreshToken(),
                'expires'       => $tokenObj->getExpires(),
            ]);
        } catch (HttpException $e) {
            return $this->redirectWithError($e->getMessage());
        } catch (\Throwable $e) {
            return $this->redirectWithError('OAuth uğursuz oldu: ' . $e->getMessage());
        }

        $html = sprintf(
            '<!DOCTYPE html><html><body><script>localStorage.setItem("lf_token", %s); window.location.href = "/dashboard";</script></body></html>',
            json_encode($tokens['token']),
        );

        $response = (new Response())->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($html);

        return $response;
    }

    private function normalizeProfile(string $provider, array $raw, string $accessToken): array
    {
        if ($provider === 'google') {
            return [
                'id'     => $raw['sub'] ?? $raw['id'] ?? '',
                'email'  => $raw['email'] ?? '',
                'name'   => $raw['name']  ?? $raw['email'] ?? '',
                'avatar' => $raw['picture'] ?? null,
            ];
        }

        $email = $raw['email'] ?? null;
        if ($email === null) {
            $emails = @json_decode(@file_get_contents('https://api.github.com/user/emails', false, stream_context_create([
                'http' => [
                    'header' => "User-Agent: LinkForge\r\nAuthorization: token {$accessToken}\r\n",
                ],
            ])) ?: '[]', true) ?: [];

            foreach ($emails as $row) {
                if (! empty($row['primary']) && ! empty($row['verified'])) {
                    $email = $row['email'];
                    break;
                }
            }
        }

        return [
            'id'     => (string) ($raw['id'] ?? ''),
            'email'  => $email ?? '',
            'name'   => $raw['name'] ?? $raw['login'] ?? '',
            'avatar' => $raw['avatar_url'] ?? null,
        ];
    }

    private function callbackUrl(string $provider): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/') . '/auth/oauth/' . $provider . '/callback';
    }

    private function redirectWithError(string $message): ResponseInterface
    {
        return (new Response())
            ->withStatus(302)
            ->withHeader('Location', '/login?error=' . urlencode($message));
    }
}
