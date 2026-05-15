<?php

declare(strict_types=1);

namespace App\Validators;

use App\DTO\CreateLinkRequest;
use App\Support\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

final class LinkValidator
{
    public function validateCreate(array $data): CreateLinkRequest
    {
        $errors = [];

        $originalUrl = trim((string) ($data['url'] ?? $data['original_url'] ?? ''));
        if (! v::url()->length(7, 2048)->validate($originalUrl)) {
            $errors['url'][] = 'Etibarlı URL ünvanı daxil edin (http:// və ya https://).';
        }

        $alias = isset($data['alias']) ? trim((string) $data['alias']) : null;
        if ($alias === '') {
            $alias = null;
        }

        $title = isset($data['title']) ? trim((string) $data['title']) : null;
        if ($title !== null && mb_strlen($title) > 255) {
            $errors['title'][] = 'Başlıq 255 simvoldan uzun ola bilməz.';
        }

        $password = isset($data['password']) ? (string) $data['password'] : null;
        if ($password === '') {
            $password = null;
        }
        if ($password !== null && mb_strlen($password) < 4) {
            $errors['password'][] = 'Şifrə ən azı 4 simvol olmalıdır.';
        }

        $expiresAt = null;
        if (! empty($data['expires_at'])) {
            try {
                $expiresAt = new \DateTimeImmutable((string) $data['expires_at']);
                if ($expiresAt <= new \DateTimeImmutable()) {
                    $errors['expires_at'][] = 'Bitmə tarixi gələcəkdə olmalıdır.';
                }
            } catch (\Throwable) {
                $errors['expires_at'][] = 'Etibarlı tarix daxil edin (ISO 8601 formatında).';
            }
        }

        $maxClicks = isset($data['max_clicks']) ? (int) $data['max_clicks'] : null;
        if ($maxClicks !== null && $maxClicks < 1) {
            $errors['max_clicks'][] = 'Maksimum klik sayı müsbət rəqəm olmalıdır.';
        }

        $utm = $data['utm'] ?? null;
        if ($utm !== null && ! is_array($utm)) {
            $errors['utm'][] = 'UTM parametrləri obyekt formatında olmalıdır.';
        }

        $ios     = isset($data['ios_deep_link'])     ? trim((string) $data['ios_deep_link'])     : null;
        $android = isset($data['android_deep_link']) ? trim((string) $data['android_deep_link']) : null;

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new CreateLinkRequest(
            originalUrl: $originalUrl,
            customAlias: $alias,
            title: $title,
            password: $password,
            expiresAt: $expiresAt,
            maxClicks: $maxClicks,
            utmParameters: is_array($utm) ? $utm : null,
            iosDeepLink: $ios ?: null,
            androidDeepLink: $android ?: null,
        );
    }
}
