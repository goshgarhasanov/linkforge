<?php

declare(strict_types=1);

namespace App\Support;

final class Translator
{
    private const DEFAULT_LOCALE = 'az';
    private const FALLBACK_LOCALE = 'en';
    private const SUPPORTED = ['az', 'en'];

    /** @var array<string, array<string, mixed>> */
    private array $translations = [];

    private string $locale;

    public function __construct(
        private readonly string $langPath,
        ?string $locale = null,
    ) {
        $this->locale = $this->normalize($locale);
        $this->load($this->locale);
        $this->load(self::FALLBACK_LOCALE);
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $this->normalize($locale);
        $this->load($this->locale);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public static function supported(): array
    {
        return self::SUPPORTED;
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public function trans(string $key, array $replace = []): string
    {
        $value = $this->lookup($key, $this->locale);

        if ($value === null && $this->locale !== self::FALLBACK_LOCALE) {
            $value = $this->lookup($key, self::FALLBACK_LOCALE);
        }

        if ($value === null) {
            return $key;
        }

        foreach ($replace as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, (string) $replacement, $value);
        }

        return $value;
    }

    private function lookup(string $key, string $locale): ?string
    {
        $segments = explode('.', $key);
        $current = $this->translations[$locale] ?? null;

        foreach ($segments as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return is_string($current) ? $current : null;
    }

    private function load(string $locale): void
    {
        if (isset($this->translations[$locale])) {
            return;
        }

        $file = $this->langPath . '/' . $locale . '.php';

        if (! file_exists($file)) {
            $this->translations[$locale] = [];
            return;
        }

        $translations = require $file;
        $this->translations[$locale] = is_array($translations) ? $translations : [];
    }

    private function normalize(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));

        if ($locale === '' || ! self::isSupported($locale)) {
            return self::DEFAULT_LOCALE;
        }

        return $locale;
    }
}
