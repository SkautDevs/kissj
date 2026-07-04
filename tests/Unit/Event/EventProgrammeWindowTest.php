<?php

declare(strict_types=1);

namespace Tests\Unit\Event;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use PHPUnit\Framework\TestCase;

class EventProgrammeWindowTest extends TestCase
{
    public function testNullWindowMeansClosed(): void
    {
        $event = new Event();
        $event->programmeRegistrationStart = null;
        $event->programmeRegistrationEnd = null;

        self::assertFalse($event->isProgrammeRegistrationOpen());
    }

    public function testOpenInsideWindow(): void
    {
        $event = new Event();
        $event->programmeRegistrationStart = DateTimeUtils::getDateTime('-1 hour');
        $event->programmeRegistrationEnd = DateTimeUtils::getDateTime('+1 hour');

        self::assertTrue($event->isProgrammeRegistrationOpen());
    }

    public function testClosedBeforeAndAfterWindow(): void
    {
        $event = new Event();
        $event->programmeRegistrationStart = DateTimeUtils::getDateTime('+1 hour');
        $event->programmeRegistrationEnd = DateTimeUtils::getDateTime('+2 hours');
        self::assertFalse($event->isProgrammeRegistrationOpen());

        $event->programmeRegistrationStart = DateTimeUtils::getDateTime('-2 hours');
        $event->programmeRegistrationEnd = DateTimeUtils::getDateTime('-1 hour');
        self::assertFalse($event->isProgrammeRegistrationOpen());
    }

    public function testHalfSetWindowMeansClosed(): void
    {
        $event = new Event();
        $event->programmeRegistrationStart = DateTimeUtils::getDateTime('-1 hour');
        $event->programmeRegistrationEnd = null;

        self::assertFalse($event->isProgrammeRegistrationOpen());
    }
}
