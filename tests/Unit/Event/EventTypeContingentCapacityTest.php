<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Ospz\EventTypeOspz;
use PHPUnit\Framework\TestCase;

class EventTypeContingentCapacityTest extends TestCase
{
    public function testCejCountsContingentsSeparately(): void
    {
        self::assertFalse((new EventTypeCej())->countContingentsTogetherForCapacity());
    }

    public function testOspzCountsContingentsSeparately(): void
    {
        self::assertFalse((new EventTypeOspz())->countContingentsTogetherForCapacity());
    }

    public function testNavigamusCountsContingentsTogether(): void
    {
        self::assertTrue((new EventTypeNavigamus())->countContingentsTogetherForCapacity());
    }
}
