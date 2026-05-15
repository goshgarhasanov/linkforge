<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ShortCodeGenerator;
use App\Support\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

final class ShortCodeGeneratorTest extends TestCase
{
    public function testRejectsReservedAlias(): void
    {
        $generator = new ShortCodeGenerator(
            length: 7,
            alphabet: '0123456789abcdef',
            reserved: ['admin', 'api'],
        );

        $this->expectException(HttpException::class);
        $generator->validateCustom('admin');
    }

    public function testRejectsInvalidCharactersInAlias(): void
    {
        $generator = new ShortCodeGenerator(
            length: 7,
            alphabet: '0123456789abcdef',
            reserved: [],
        );

        $this->expectException(HttpException::class);
        $generator->validateCustom('has space!');
    }

    public function testAcceptsValidAlias(): void
    {
        $generator = new ShortCodeGenerator(
            length: 7,
            alphabet: '0123456789abcdef',
            reserved: [],
        );

        $this->markTestSkipped('Database connection required — covered by feature tests.');
    }
}
