<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\OAuthAccount;
use App\Models\User;
use App\Support\Exceptions\HttpException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;

final class OAuthService
{
    public function __construct(
        private readonly TokenService $tokens,
    ) {
    }

    public function provider(string $name, string $redirectUri): AbstractProvider
    {
        return match ($name) {
            'google' => new Google([
                'clientId'     => $_ENV['GOOGLE_CLIENT_ID']     ?? '',
                'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
                'redirectUri'  => $redirectUri,
            ]),
            'github' => new Github([
                'clientId'     => $_ENV['GITHUB_CLIENT_ID']     ?? '',
                'clientSecret' => $_ENV['GITHUB_CLIENT_SECRET'] ?? '',
                'redirectUri'  => $redirectUri,
            ]),
            default => throw new HttpException(404, "OAuth provayder '{$name}' dəstəklənmir."),
        };
    }

    public function loginOrRegister(string $providerName, array $profile, array $tokens): array
    {
        $providerUserId = (string) $profile['id'];
        $email          = mb_strtolower(trim((string) ($profile['email'] ?? '')));
        $name           = (string) ($profile['name'] ?? $email);
        $avatar         = $profile['avatar'] ?? null;

        $oauth = OAuthAccount::query()
            ->where('provider', $providerName)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($oauth instanceof OAuthAccount) {
            $user = $oauth->user;
            $this->touchTokens($oauth, $tokens);
        } else {
            if ($email === '') {
                throw new HttpException(422, 'Provayder e-poçt qaytarmadı. Manual qeydiyyat istifadə edin.');
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user instanceof User) {
                $user = new User([
                    'name'              => $name,
                    'email'             => $email,
                    'password_hash'     => password_hash(bin2hex(random_bytes(16)), PASSWORD_ARGON2ID),
                    'role'              => UserRole::FreeUser,
                    'is_active'         => true,
                    'email_verified_at' => now(),
                    'avatar_url'        => $avatar,
                ]);
                $user->save();
            }

            OAuthAccount::query()->create([
                'user_id'          => $user->id,
                'provider'         => $providerName,
                'provider_user_id' => $providerUserId,
                'provider_email'   => $email,
                'access_token'     => $tokens['access_token']  ?? null,
                'refresh_token'    => $tokens['refresh_token'] ?? null,
                'expires_at'       => isset($tokens['expires']) ? (new \DateTimeImmutable())->setTimestamp($tokens['expires']) : null,
                'raw_profile'      => $profile,
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->tokens->issue($user);
    }

    private function touchTokens(OAuthAccount $oauth, array $tokens): void
    {
        $oauth->forceFill([
            'access_token'  => $tokens['access_token']  ?? $oauth->access_token,
            'refresh_token' => $tokens['refresh_token'] ?? $oauth->refresh_token,
            'expires_at'    => isset($tokens['expires']) ? (new \DateTimeImmutable())->setTimestamp($tokens['expires']) : $oauth->expires_at,
        ])->save();
    }
}
