<?php

declare(strict_types=1);

namespace Tests\Unit\Skautis;

use DateTimeImmutable;
use kissj\Participant\Country;
use kissj\Participant\Gender;
use kissj\Skautis\SkautisMemberData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SkautisMemberDataTest extends TestCase
{
    #[DataProvider('permanentResidenceProvider')]
    public function testGetPermanentResidence(string $street, string $city, string $postcode, string $expected): void
    {
        $dto = new SkautisMemberData(
            id: 1,
            firstName: 'Jan',
            lastName: 'Novák',
            nickName: 'Honza',
            birthday: new DateTimeImmutable('2000-01-01'),
            street: $street,
            city: $city,
            postcode: $postcode,
            state: 'Česká republika',
            sex: 'muž',
        );

        self::assertSame($expected, $dto->getPermanentResidence());
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function permanentResidenceProvider(): array
    {
        return [
            'all filled' => ['Hlavní 1', 'Praha', '11000', 'Hlavní 1, Praha, 11000'],
            'all empty' => ['', '', '', ''],
            'only city' => ['', 'Praha', '', ', Praha, '],
            'street and city' => ['Hlavní 1', 'Praha', '', 'Hlavní 1, Praha, '],
        ];
    }

    #[DataProvider('genderProvider')]
    public function testGetGender(string $sex, Gender $expected): void
    {
        $dto = new SkautisMemberData(1, 'Jan', 'Novák', '', new DateTimeImmutable('2000-01-01'), '', '', '', '', $sex);

        self::assertSame($expected, $dto->getGender());
    }

    /**
     * @return array<string, array{string, Gender}>
     */
    public static function genderProvider(): array
    {
        return [
            'czech male' => ['muž', Gender::Man],
            'czech female' => ['žena', Gender::Woman],
            'english male' => ['male', Gender::Man],
            'unknown' => ['', Gender::Other],
        ];
    }

    #[DataProvider('countryProvider')]
    public function testGetCountry(string $state, Country $expected): void
    {
        $dto = new SkautisMemberData(1, 'Jan', 'Novák', '', new DateTimeImmutable('2000-01-01'), '', '', '', $state, '');

        self::assertSame($expected, $dto->getCountry());
    }

    /**
     * @return array<string, array{string, Country}>
     */
    public static function countryProvider(): array
    {
        return [
            'czech' => ['Česká republika', Country::CzechRepublic],
            'czech english' => ['Czech Republic', Country::CzechRepublic],
            'czechia' => ['Czechia', Country::CzechRepublic],
            'slovakia czech' => ['Slovensko', Country::Slovakia],
            'slovakia english' => ['Slovakia', Country::Slovakia],
            'other' => ['Německo', Country::Other],
            'empty' => ['', Country::Other],
        ];
    }
}
