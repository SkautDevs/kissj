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
        $event->programmeEnrolmentStart = null;
        $event->programmeEnrolmentEnd = null;

        self::assertFalse($event->isProgrammeEnrolmentOpen());
    }

    public function testOpenInsideWindow(): void
    {
        $event = new Event();
        $event->programmeEnrolmentStart = DateTimeUtils::getDateTime('-1 hour');
        $event->programmeEnrolmentEnd = DateTimeUtils::getDateTime('+1 hour');

        self::assertTrue($event->isProgrammeEnrolmentOpen());
    }

    public function testClosedBeforeAndAfterWindow(): void
    {
        $event = new Event();
        $event->programmeEnrolmentStart = DateTimeUtils::getDateTime('+1 hour');
        $event->programmeEnrolmentEnd = DateTimeUtils::getDateTime('+2 hours');
        self::assertFalse($event->isProgrammeEnrolmentOpen());

        $event->programmeEnrolmentStart = DateTimeUtils::getDateTime('-2 hours');
        $event->programmeEnrolmentEnd = DateTimeUtils::getDateTime('-1 hour');
        self::assertFalse($event->isProgrammeEnrolmentOpen());
    }

    public function testHalfSetWindowMeansClosed(): void
    {
        $event = new Event();
        $event->programmeEnrolmentStart = DateTimeUtils::getDateTime('-1 hour');
        $event->programmeEnrolmentEnd = null;

        self::assertFalse($event->isProgrammeEnrolmentOpen());
    }
}
