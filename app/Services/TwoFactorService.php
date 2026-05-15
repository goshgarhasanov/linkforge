<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\Exceptions\HttpException;
use RobThree\Auth\TwoFactorAuth;

final class TwoFactorService
{
    private readonly TwoFactorAuth $tfa;

    public function __construct()
    {
        $this->tfa = new TwoFactorAuth(issuer: 'LinkForge');
    }

    /**
     * @return array{secret: string, qr_data_uri: string}
     */
    public function beginEnrollment(User $user): array
    {
        $secret = $this->tfa->createSecret();
        $qrUri  = $this->tfa->getQRCodeImageAsDataUri($user->email, $secret);

        return ['secret' => $secret, 'qr_data_uri' => $qrUri];
    }

    public function confirmEnrollment(User $user, string $secret, string $code): void
    {
        if (! $this->tfa->verifyCode($secret, $code)) {
            throw new HttpException(422, 'Kod yanlışdır. Yenidən cəhd edin.');
        }

        $user->forceFill([
            'two_factor_secret'  => $secret,
            'two_factor_enabled' => true,
        ])->save();
    }

    public function disable(User $user, string $code): void
    {
        if ($user->two_factor_secret === null || ! $this->tfa->verifyCode($user->two_factor_secret, $code)) {
            throw new HttpException(422, 'Kod yanlışdır.');
        }

        $user->forceFill([
            'two_factor_secret'  => null,
            'two_factor_enabled' => false,
        ])->save();
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_enabled || $user->two_factor_secret === null) {
            return true;
        }

        return $this->tfa->verifyCode($user->two_factor_secret, $code);
    }
}
