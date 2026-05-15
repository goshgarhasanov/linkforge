<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

class HttpException extends \RuntimeException
{
    public function __construct(int $statusCode, string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function notFound(string $message = 'Resurs tapılmadı.'): self
    {
        return new self(404, $message);
    }

    public static function unauthorized(string $message = 'Avtorizasiya tələb olunur.'): self
    {
        return new self(401, $message);
    }

    public static function forbidden(string $message = 'Bu əməliyyat üçün icazəniz yoxdur.'): self
    {
        return new self(403, $message);
    }

    public static function conflict(string $message = 'Resurs artıq mövcuddur.'): self
    {
        return new self(409, $message);
    }
}
