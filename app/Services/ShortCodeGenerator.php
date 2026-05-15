<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Link;
use App\Support\Exceptions\HttpException;

final class ShortCodeGenerator
{
    private const MAX_ATTEMPTS = 8;

    /**
     * @param string[] $reserved
     */
    public function __construct(
        private readonly int $length,
        private readonly string $alphabet,
        private readonly array $reserved,
    ) {
    }

    public function generate(): string
    {
        $alphabetLength = strlen($this->alphabet);

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = '';
            for ($i = 0; $i < $this->length; $i++) {
                $code .= $this->alphabet[random_int(0, $alphabetLength - 1)];
            }

            if ($this->isAvailable($code)) {
                return $code;
            }
        }

        throw new \RuntimeException('Unikal qısa kod yarada bilmədik. Zəhmət olmasa, yenidən cəhd edin.');
    }

    public function validateCustom(string $code): string
    {
        $code = trim($code);

        if (! preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $code)) {
            throw new HttpException(422, 'Xüsusi alias yalnız hərf, rəqəm, tire və alt xətt ehtiva edə bilər (3-32 simvol).');
        }

        if (in_array(strtolower($code), array_map('strtolower', $this->reserved), true)) {
            throw HttpException::conflict('Bu alias platforma tərəfindən qorunur. Başqa birini seçin.');
        }

        if (! $this->isAvailable($code)) {
            throw HttpException::conflict('Bu alias artıq istifadədədir. Başqa birini seçin.');
        }

        return $code;
    }

    private function isAvailable(string $code): bool
    {
        return ! Link::query()->where('short_code', $code)->withTrashed()->exists();
    }
}
