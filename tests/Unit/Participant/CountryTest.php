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
            'czech Česká republika' => ['Česká republika', Country::CzechRepublic],
            'czech Česká Republika' => ['Česká Republika', Country::CzechRepublic],
            'czech Česká bRepublika' => ['Česká bRepublika', Country::CzechRepublic],
            'czech Cz' => ['Cz', Country::CzechRepublic],
            'czech CZ' => ['CZ', Country::CzechRepublic],
            'czech ČR' => ['ČR', Country::CzechRepublic],
            'czech CZE' => ['CZE', Country::CzechRepublic],
            'czech Česko' => ['Česko', Country::CzechRepublic],
            'czech ČESKO' => ['ČESKO', Country::CzechRepublic],
            'czech Czechia' => ['Czechia', Country::CzechRepublic],
            'czech Czech Republic' => ['Czech Republic', Country::CzechRepublic],
            'czech ceska republika' => ['ceska republika', Country::CzechRepublic],
            'czech padded Česká rep.' => [' Česká rep. ', Country::CzechRepublic],
            'slovak Slovensko' => ['Slovensko', Country::Slovakia],
            'slovak Slovenská republika' => ['Slovenská republika', Country::Slovakia],
            'slovak SR' => ['SR', Country::Slovakia],
            'slovak SK' => ['SK', Country::Slovakia],
            'slovak SVK' => ['SVK', Country::Slovakia],
            'slovak Slovakia' => ['Slovakia', Country::Slovakia],
            'slovak Slovak Republic' => ['Slovak Republic', Country::Slovakia],
            'other null' => [null, Country::Other],
            'other empty string' => ['', Country::Other],
            'other whitespace only' => ['  ', Country::Other],
            'other ...' => ['...', Country::Other],
            'other Polsko' => ['Polsko', Country::Other],
            'other Slovinsko' => ['Slovinsko', Country::Other],
            'other Slovenia' => ['Slovenia', Country::Other],
            'other Slovenija' => ['Slovenija', Country::Other],
            'other Чехия' => ['Чехия', Country::Other],
        ];
    }
}
