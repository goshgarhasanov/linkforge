<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Exceptions\HttpException;
use Illuminate\Database\Capsule\Manager as DB;

final class AuthService
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_WINDOW_MINUTES = 15;

    public function __construct(
        private readonly TokenService $tokens,
    ) {
    }

    public function register(string $name, string $email, string $password): User
    {
        $email = mb_strtolower(trim($email));

        if (User::query()->where('email', $email)->exists()) {
            throw HttpException::conflict('Bu e-poçt ünvanı artıq qeydiyyatdan keçib.');
        }

        $user = new User([
            'name'          => trim($name),
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
            'role'          => UserRole::FreeUser,
            'is_active'     => true,
        ]);
        $user->save();

        return $user;
    }

    public function login(string $email, string $password, string $ipAddress, ?string $userAgent): array
    {
        $email = mb_strtolower(trim($email));

        $this->guardAgainstLockout($email, $ipAddress);

        $user = User::query()->where('email', $email)->first();
        $success = $user instanceof User && $user->is_active && $user->verifyPassword($password);

        $this->recordAttempt($email, $ipAddress, $userAgent, $success);

        if (! $success) {
            throw HttpException::unauthorized('E-poçt və ya şifrə yanlışdır.');
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ])->save();

        return $this->tokens->issue($user);
    }

    public function authenticate(string $bearerToken): User
    {
        $payload = $this->tokens->decode($bearerToken);
        $user = User::query()->find((int) $payload->sub);

        if (! $user instanceof User || ! $user->is_active) {
            throw HttpException::unauthorized('İstifadəçi tapılmadı və ya aktiv deyil.');
        }

        return $user;
    }

    private function guardAgainstLockout(string $email, string $ipAddress): void
    {
        $threshold = now()->subMinutes(self::LOCKOUT_WINDOW_MINUTES);

        $failedAttempts = DB::table('login_attempts')
            ->where('ip_address', $ipAddress)
            ->where('was_successful', false)
            ->where('attempted_at', '>=', $threshold)
            ->count();

        if ($failedAttempts >= self::MAX_FAILED_ATTEMPTS) {
            throw new HttpException(
                429,
                'Çox sayda uğursuz cəhd. Zəhmət olmasa, ' . self::LOCKOUT_WINDOW_MINUTES . ' dəqiqə sonra yenidən cəhd edin.',
            );
        }
    }

    private function recordAttempt(string $email, string $ipAddress, ?string $userAgent, bool $success): void
    {
        DB::table('login_attempts')->insert([
            'email'          => $email,
            'ip_address'     => $ipAddress,
            'was_successful' => $success,
            'user_agent'     => $userAgent,
            'attempted_at'   => now(),
        ]);
    }
}
