<?php

declare(strict_types=1);

namespace Tests\Unit\Participant;

use kissj\Participant\Patrol\PatrolParticipant;
use PHPUnit\Framework\TestCase;

class DetachedParticipantTest extends TestCase
{
    public function testGetTshirtReturnsNullOnDetachedEntity(): void
    {
        $participant = new PatrolParticipant();

        self::assertNull($participant->getTshirt());
    }

    public function testGetTshirtShapeReturnsNullOnDetachedEntity(): void
    {
        $participant = new PatrolParticipant();

        self::assertNull($participant->getTshirtShape());
    }

    public function testGetTshirtSizeReturnsNullOnDetachedEntity(): void
    {
        $participant = new PatrolParticipant();

        self::assertNull($participant->getTshirtSize());
    }

    public function testPreferredPositionReturnsEmptyArrayOnDetachedEntity(): void
    {
        $participant = new PatrolParticipant();

        /** @var list<string> $value */
        $value = $participant->preferredPosition;

        self::assertSame([], $value);
    }

    public function testGetValueForFieldReturnsNullOnDetachedEntity(): void
    {
        $participant = new PatrolParticipant();

        self::assertNull($participant->getValueForField('firstName'));
        self::assertNull($participant->getValueForField('birthDate'));
        self::assertNull($participant->getValueForField('arrivalDate'));
    }
}
