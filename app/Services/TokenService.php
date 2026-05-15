<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\Exceptions\InvalidTokenException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class TokenService
{
    public function __construct(
        private readonly string $secret,
        private readonly string $algorithm,
        private readonly int $ttl,
        private readonly string $issuer,
        private readonly string $audience,
    ) {
        if ($this->secret === '') {
            throw new \RuntimeException('JWT_SECRET dəyişəni təyin edilməyib.');
        }
    }

    public function issue(User $user): array
    {
        $now = time();
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttl,
            'sub' => (string) $user->id,
            'uuid' => $user->uuid,
            'role' => $user->role->value,
        ];

        return [
            'token'      => JWT::encode($payload, $this->secret, $this->algorithm),
            'token_type' => 'Bearer',
            'expires_in' => $this->ttl,
        ];
    }

    public function decode(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (\Throwable $e) {
            throw new InvalidTokenException('Token etibarsızdır və ya vaxtı keçib.', previous: $e);
        }
    }

    public static function generatePersonalToken(): array
    {
        $plain  = bin2hex(random_bytes(32));
        $hash   = hash('sha256', $plain);
        $prefix = 'lf_' . substr($plain, 0, 8);

        return [
            'plain'  => 'lf_' . $plain,
            'hash'   => $hash,
            'prefix' => $prefix,
        ];
    }
}
