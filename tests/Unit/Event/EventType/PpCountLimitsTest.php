<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\Event;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use PHPUnit\Framework\TestCase;

class PpCountLimitsTest extends TestCase
{
    public function testTroopLeaderGetsTroopLimits(): void
    {
        $eventType = new EventTypeDefault();
        $event = $this->createEventWithLimits();

        self::assertSame(5, $eventType->getMinimalPpCount($event, new TroopLeader()));
        self::assertSame(20, $eventType->getMaximalPpCount($event, new TroopLeader()));
    }

    public function testPatrolLeaderGetsPatrolLimits(): void
    {
        $eventType = new EventTypeDefault();
        $event = $this->createEventWithLimits();

        self::assertSame(2, $eventType->getMinimalPpCount($event, new PatrolLeader()));
        self::assertSame(8, $eventType->getMaximalPpCount($event, new PatrolLeader()));
    }

    public function testTroopParticipantGetsPatrolLimits(): void
    {
        $eventType = new EventTypeDefault();
        $event = $this->createEventWithLimits();

        self::assertSame(2, $eventType->getMinimalPpCount($event, new TroopParticipant()));
        self::assertSame(8, $eventType->getMaximalPpCount($event, new TroopParticipant()));
    }

    public function testTroopLeaderWithoutTroopLimitsIsUnlimited(): void
    {
        $eventType = new EventTypeDefault();
        $event = $this->createEventWithLimits();
        $event->minimalTroopParticipantsCount = null;
        $event->maximalTroopParticipantsCount = null;

        self::assertSame(0, $eventType->getMinimalPpCount($event, new TroopLeader()));
        self::assertNull($eventType->getMaximalPpCount($event, new TroopLeader()));
    }

    private function createEventWithLimits(): Event
    {
        $event = new Event();
        $event->minimalPatrolParticipantsCount = 2;
        $event->maximalPatrolParticipantsCount = 8;
        $event->minimalTroopParticipantsCount = 5;
        $event->maximalTroopParticipantsCount = 20;

        return $event;
    }
}
