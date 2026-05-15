<?php

declare(strict_types=1);

namespace App\DTO;

final class CreateLinkRequest
{
    /**
     * @param array<string, string>|null $utmParameters
     */
    public function __construct(
        public readonly string $originalUrl,
        public readonly ?string $customAlias,
        public readonly ?string $title,
        public readonly ?string $password,
        public readonly ?\DateTimeImmutable $expiresAt,
        public readonly ?int $maxClicks,
        public readonly ?array $utmParameters,
        public readonly ?string $iosDeepLink,
        public readonly ?string $androidDeepLink,
    ) {
    }
}
