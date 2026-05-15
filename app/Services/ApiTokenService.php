<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use App\Support\Exceptions\HttpException;
use App\Support\Exceptions\ValidationException;

final class ApiTokenService
{
    private const ALLOWED_ABILITIES = ['read', 'write', 'delete', '*'];

    /**
     * @param string[] $abilities
     * @return array{token: ApiToken, plain_text: string}
     */
    public function create(User $user, string $name, array $abilities, ?\DateTimeImmutable $expiresAt = null): array
    {
        $name = trim($name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            throw new ValidationException(['name' => ['Token adı 2 ilə 120 simvol arasında olmalıdır.']]);
        }

        $invalid = array_diff($abilities, self::ALLOWED_ABILITIES);
        if ($invalid !== []) {
            throw new ValidationException(['abilities' => ['Etibarsız icazələr: ' . implode(', ', $invalid)]]);
        }

        $generated = TokenService::generatePersonalToken();

        $token = new ApiToken([
            'user_id'      => $user->id,
            'name'         => $name,
            'token_hash'   => $generated['hash'],
            'token_prefix' => $generated['prefix'],
            'abilities'    => array_values(array_unique($abilities)),
            'expires_at'   => $expiresAt,
        ]);
        $token->save();

        return [
            'token'      => $token,
            'plain_text' => $generated['plain'],
        ];
    }

    public function revoke(User $user, int $tokenId): void
    {
        $token = ApiToken::query()
            ->where('id', $tokenId)
            ->where('user_id', $user->id)
            ->first();

        if (! $token instanceof ApiToken) {
            throw HttpException::notFound('Token tapılmadı.');
        }

        $token->forceFill(['revoked_at' => now()])->save();
    }
}
