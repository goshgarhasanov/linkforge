<?php

declare(strict_types=1);

namespace App\Support;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('t', fn (string $key, array $replace = []): string => $this->translator->trans($key, $replace)),
            new TwigFunction('locale', fn (): string => $this->translator->getLocale()),
        ];
    }
}
