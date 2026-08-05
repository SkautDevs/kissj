<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use kissj\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PaymentCurrencyNormalizationTest extends TestCase
{
    /**
     * @return array<string, array{string|null, string|null}>
     */
    public static function currencyProvider(): array
    {
        return [
            'czech symbol' => ['Kč', 'CZK'],
            'czech symbol lowercase' => ['kč', 'CZK'],
            'iso czk' => ['CZK', 'CZK'],
            'iso czk lowercase' => ['czk', 'CZK'],
            'euro sign' => ['€', 'EUR'],
            'euro word' => ['euro', 'EUR'],
            'euro word capitalized' => ['Euro', 'EUR'],
            'iso eur' => ['EUR', 'EUR'],
            'other iso passthrough' => ['usd', 'USD'],
            'other iso uppercase' => ['PLN', 'PLN'],
            'whitespace trimmed' => [' CZK ', 'CZK'],
            'null' => [null, null],
            'empty string' => ['', null],
            'garbage' => ['Kc$', null],
            'two letters' => ['ab', null],
            'four letters' => ['abcd', null],
        ];
    }

    #[DataProvider('currencyProvider')]
    public function testNormalizeCurrency(?string $input, ?string $expected): void
    {
        self::assertSame($expected, Payment::normalizeCurrency($input));
    }
}
