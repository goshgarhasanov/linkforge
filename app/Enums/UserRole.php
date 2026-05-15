<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case ProUser    = 'pro_user';
    case FreeUser   = 'free_user';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Admin      => 'Administrator',
            self::ProUser    => 'Pro İstifadəçi',
            self::FreeUser   => 'Pulsuz İstifadəçi',
        };
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function linkLimit(): ?int
    {
        return match ($this) {
            self::SuperAdmin, self::Admin, self::ProUser => null,
            self::FreeUser => 50,
        };
    }
}
