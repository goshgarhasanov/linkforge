<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Free       = 'free';
    case Pro        = 'pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Free       => 'Pulsuz',
            self::Pro        => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }

    public function monthlyPrice(): float
    {
        return match ($this) {
            self::Free       => 0.0,
            self::Pro        => 19.0,
            self::Enterprise => 99.0,
        };
    }

    public function linkLimit(): ?int
    {
        return match ($this) {
            self::Free       => 50,
            self::Pro        => 5000,
            self::Enterprise => null,
        };
    }

    public function features(): array
    {
        return match ($this) {
            self::Free => [
                '50 link/ay',
                'Əsas analitika',
                'QR kod generasiyası',
            ],
            self::Pro => [
                '5,000 link/ay',
                'Genişləndirilmiş analitika',
                'Xüsusi alias',
                'API girişi',
                'Şifrə qoruması',
                'Webhook-lar',
            ],
            self::Enterprise => [
                'Limitsiz link',
                'Prioritet dəstək',
                'SSO və SAML',
                'SLA təminatı',
                'Dedicated infrastruktur',
                'Custom integrations',
            ],
        };
    }
}
