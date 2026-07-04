<?php

declare(strict_types=1);

namespace Tests\Unit\Skautis;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Country;
use kissj\Participant\Gender;
use kissj\Skautis\SkautisUserData;
use PHPUnit\Framework\TestCase;

class SkautisUserDataTest extends TestCase
{
    public function testCountryDefaultsToOther(): void
    {
        $dto = self::buildDto();

        self::assertSame(Country::Other, $dto->country);
    }

    public function testCountryIsExposed(): void
    {
        $dto = self::buildDto(country: Country::CzechRepublic);

        self::assertSame(Country::CzechRepublic, $dto->country);
    }

    private static function buildDto(
        Gender $gender = Gender::Other,
        Country $country = Country::Other,
    ): SkautisUserData {
        return new SkautisUserData(
            skautisId: 1,
            skautisUserName: 'jan.novak',
            skautisIdPerson: 2,
            firstName: 'Jan',
            lastName: 'Novák',
            nickName: '',
            birthday: DateTimeUtils::getDateTime('2000-01-01'),
            email: 'jan@example.com',
            phone: '',
            street: '',
            city: '',
            postCode: '',
            hasMembership: true,
            unitName: '',
            gender: $gender,
            country: $country,
        );
    }
}
