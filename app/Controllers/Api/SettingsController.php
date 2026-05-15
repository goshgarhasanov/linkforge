<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\ApiToken;
use App\Models\User;
use App\Services\ApiTokenService;
use App\Support\Exceptions\HttpException;
use App\Support\Exceptions\ValidationException;
use App\Support\Http\JsonResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SettingsController
{
    public function __construct(
        private readonly ApiTokenService $tokenService,
    ) {
    }

    public function updateProfile(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $name = trim((string) ($body['name'] ?? ''));
        $locale = mb_substr((string) ($body['locale'] ?? $user->locale), 0, 5);

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            return JsonResponder::error('Ad 2 ilə 120 simvol arasında olmalıdır.', 422);
        }

        $user->forceFill(['name' => $name, 'locale' => $locale])->save();

        return JsonResponder::success([
            'name'   => $user->name,
            'email'  => $user->email,
            'locale' => $user->locale,
        ]);
    }

    public function changePassword(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $current = (string) ($body['current_password'] ?? '');
        $new     = (string) ($body['new_password']     ?? '');

        if (! $user->verifyPassword($current)) {
            return JsonResponder::error('Cari şifrə yanlışdır.', 401);
        }

        if (mb_strlen($new) < 8 || ! preg_match('/[A-Z]/', $new) || ! preg_match('/[a-z]/', $new) || ! preg_match('/\d/', $new)) {
            return JsonResponder::error('Yeni şifrə ən azı 8 simvol, böyük/kiçik hərf və rəqəm ehtiva etməlidir.', 422);
        }

        $user->forceFill(['password_hash' => password_hash($new, PASSWORD_ARGON2ID)])->save();

        return JsonResponder::success(['updated' => true]);
    }

    public function listTokens(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $tokens = ApiToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->orderByDesc('created_at')
            ->get();

        return JsonResponder::success(
            $tokens->map(static fn (ApiToken $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'prefix'       => $t->token_prefix,
                'abilities'    => $t->abilities,
                'last_used_at' => $t->last_used_at?->toIso8601String(),
                'expires_at'   => $t->expires_at?->toIso8601String(),
                'created_at'   => $t->created_at?->toIso8601String(),
            ])->all(),
        );
    }

    public function createToken(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $body = (array) $request->getParsedBody();

        $name = (string) ($body['name'] ?? '');
        $abilities = (array) ($body['abilities'] ?? ['read']);

        $expiresAt = null;
        if (! empty($body['expires_at'])) {
            try {
                $expiresAt = new \DateTimeImmutable((string) $body['expires_at']);
            } catch (\Throwable) {
                return JsonResponder::error('Etibarsız bitmə tarixi.', 422);
            }
        }

        try {
            $created = $this->tokenService->create($user, $name, $abilities, $expiresAt);
        } catch (ValidationException $e) {
            return JsonResponder::error($e->getMessage(), 422, $e->errors);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::created([
            'id'        => $created['token']->id,
            'name'      => $created['token']->name,
            'abilities' => $created['token']->abilities,
            'token'     => $created['plain_text'],
            'warning'   => 'Bu token yalnız bir dəfə göstərilir — onu indi təhlükəsiz yerdə saxlayın.',
        ]);
    }

    public function revokeToken(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        try {
            $this->tokenService->revoke($user, (int) $args['id']);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        return JsonResponder::noContent();
    }
}
