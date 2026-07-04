<?php

declare(strict_types=1);

namespace Tests\Unit\Skautis;

use kissj\Application\DateTimeUtils;
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
            birthday: DateTimeUtils::getDateTime('2000-01-01'),
            street: $street,
            city: $city,
            postcode: $postcode,
            country: Country::CzechRepublic,
            gender: Gender::Man,
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
            'only city' => ['', 'Praha', '', 'Praha'],
            'street and city' => ['Hlavní 1', 'Praha', '', 'Hlavní 1, Praha'],
        ];
    }

    public function testGenderAndCountryAreExposed(): void
    {
        $dto = new SkautisMemberData(
            1,
            'Jan',
            'Novák',
            '',
            DateTimeUtils::getDateTime('2000-01-01'),
            '',
            '',
            '',
            Country::Slovakia,
            Gender::Woman,
        );

        self::assertSame(Gender::Woman, $dto->gender);
        self::assertSame(Country::Slovakia, $dto->country);
    }
}
