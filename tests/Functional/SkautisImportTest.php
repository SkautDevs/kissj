<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\Participant\Country;
use kissj\Participant\Gender;
use kissj\Participant\Patrol\PatrolService;
use kissj\Skautis\SkautisMemberData;
use PHPUnit\Framework\TestCase;

class SkautisImportTest extends TestCase
{
    public function testMatchMembersIdentifiesLeaderAndExisting(): void
    {
        $members = [
            new SkautisMemberData(10, 'Jan', 'Novák', 'Honza', new DateTimeImmutable('1990-01-01'), 'Hlavní 1', 'Praha', '11000', 'Česká republika', 'muž'),
            new SkautisMemberData(20, 'Eva', 'Dvořáková', 'Evička', new DateTimeImmutable('2005-06-15'), 'Krátká 2', 'Brno', '60200', 'Česká republika', 'žena'),
            new SkautisMemberData(30, 'Petr', 'Svoboda', '', new DateTimeImmutable('2006-11-20'), '', '', '', 'Česká republika', 'muž'),
        ];

        $result = PatrolService::matchMembersAgainstExisting(
            $members,
            'Jan',
            'Novák',
            new DateTimeImmutable('1990-01-01'),
            [
                ['firstName' => 'Eva', 'lastName' => 'Dvořáková', 'birthDate' => new DateTimeImmutable('2005-06-15')],
            ],
        );

        self::assertSame('leader', $result[10]);
        self::assertSame('added', $result[20]);
        self::assertNull($result[30]);
    }

    public function testSkautisMemberDataPrefillMapping(): void
    {
        $member = new SkautisMemberData(
            id: 42,
            firstName: 'Petr',
            lastName: 'Svoboda',
            nickName: 'Péťa',
            birthday: new DateTimeImmutable('2006-11-20'),
            street: 'Krátká 5',
            city: 'Brno',
            postcode: '60200',
            state: 'Česká republika',
            sex: 'muž',
        );

        self::assertSame('Krátká 5, Brno, 60200', $member->getPermanentResidence());
        self::assertSame(Gender::Man, $member->getGender());
        self::assertSame(Country::CzechRepublic, $member->getCountry());
    }
}
