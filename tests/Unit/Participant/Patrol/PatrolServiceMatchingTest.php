<?php

declare(strict_types=1);

namespace Tests\Unit\Participant\Patrol;

use DateTimeImmutable;
use kissj\Participant\Patrol\PatrolService;
use kissj\Skautis\SkautisMemberData;
use PHPUnit\Framework\TestCase;

class PatrolServiceMatchingTest extends TestCase
{
    public function testMatchExistingParticipants(): void
    {
        $leaderFirstName = 'Jan';
        $leaderLastName = 'Novák';
        $leaderBirthday = new DateTimeImmutable('1990-05-15');

        $participantFirstName = 'Eva';
        $participantLastName = 'Dvořáková';
        $participantBirthday = new DateTimeImmutable('2005-03-10');

        $members = [
            new SkautisMemberData(1, $leaderFirstName, $leaderLastName, '', $leaderBirthday, '', '', '', '', 'muž'),
            new SkautisMemberData(2, $participantFirstName, $participantLastName, '', $participantBirthday, '', '', '', '', 'žena'),
            new SkautisMemberData(3, 'Petr', 'Svoboda', '', new DateTimeImmutable('2006-01-01'), '', '', '', '', 'muž'),
        ];

        $result = PatrolService::matchMembersAgainstExisting(
            $members,
            $leaderFirstName,
            $leaderLastName,
            $leaderBirthday,
            [
                ['firstName' => $participantFirstName, 'lastName' => $participantLastName, 'birthDate' => $participantBirthday],
            ],
        );

        self::assertSame('leader', $result[1]);
        self::assertSame('added', $result[2]);
        self::assertNull($result[3]);
    }

    public function testMatchWithNoExistingParticipants(): void
    {
        $members = [
            new SkautisMemberData(1, 'Jan', 'Novák', '', new DateTimeImmutable('1990-05-15'), '', '', '', '', 'muž'),
            new SkautisMemberData(2, 'Petr', 'Svoboda', '', new DateTimeImmutable('2006-01-01'), '', '', '', '', 'muž'),
        ];

        $result = PatrolService::matchMembersAgainstExisting(
            $members,
            'Jan',
            'Novák',
            new DateTimeImmutable('1990-05-15'),
            [],
        );

        self::assertSame('leader', $result[1]);
        self::assertNull($result[2]);
    }

    public function testMatchWithNullBirthDateInExistingParticipant(): void
    {
        $members = [
            new SkautisMemberData(1, 'Eva', 'Dvořáková', '', new DateTimeImmutable('2005-03-10'), '', '', '', '', 'žena'),
        ];

        $result = PatrolService::matchMembersAgainstExisting(
            $members,
            'Jan',
            'Novák',
            new DateTimeImmutable('1990-05-15'),
            [
                ['firstName' => 'Eva', 'lastName' => 'Dvořáková', 'birthDate' => null],
            ],
        );

        self::assertNull($result[1]);
    }
}
