<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

final class ValidationException extends \RuntimeException
{
    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(
        public readonly array $errors,
        string $message = 'Daxil edilmiş məlumatlar etibarlı deyil.',
    ) {
        parent::__construct($message, 422);
    }
}
