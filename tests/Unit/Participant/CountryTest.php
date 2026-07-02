<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testBackedValues(): void
    {
        self::assertSame('detail.countryCzechRepublic', Country::CzechRepublic->value);
        self::assertSame('detail.countrySlovakia', Country::Slovakia->value);
        self::assertSame('detail.countryOther', Country::Other->value);
    }

    #[DataProvider('skautisStateProvider')]
    public function testFromSkautisState(?string $state, Country $expected): void
    {
        self::assertSame($expected, Country::fromSkautisState($state));
    }

    /**
     * @return array<string, array{?string, Country}>
     */
    public static function skautisStateProvider(): array
    {
        return [
            // exact strings skautIS returns in the person State field
            'czech' => ['Česká republika', Country::CzechRepublic],
            'slovak' => ['Slovensko', Country::Slovakia],
            'null' => [null, Country::Other],
            'empty string' => ['', Country::Other],
            'english name is not accepted' => ['Czech Republic', Country::Other],
            'unknown value' => ['Polsko', Country::Other],
        ];
    }
}
