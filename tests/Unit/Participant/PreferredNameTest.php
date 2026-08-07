<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Patrol\PatrolParticipant;
use PHPUnit\Framework\TestCase;

class PreferredNameTest extends TestCase
{
    public function testNicknameWins(): void
    {
        $participant = new PatrolParticipant();
        $participant->firstName = 'Josef';
        $participant->lastName = 'Šusta';
        $participant->nickname = 'Pepa';

        self::assertSame('Pepa', $participant->getPreferredName());
    }

    public function testFirstNameIsUsedWithoutNickname(): void
    {
        $participant = new PatrolParticipant();
        $participant->firstName = 'Josef';
        $participant->lastName = 'Šusta';
        $participant->nickname = '';

        self::assertSame('Josef', $participant->getPreferredName());
    }

    public function testFullNameIsUsedWithoutNicknameAndFirstName(): void
    {
        $participant = new PatrolParticipant();
        $participant->lastName = 'Šusta';

        self::assertSame('Šusta', $participant->getPreferredName());
    }

    public function testEmptyStringWhenEveryNameIsMissing(): void
    {
        $participant = new PatrolParticipant();

        self::assertSame('', $participant->getPreferredName());
    }
}
