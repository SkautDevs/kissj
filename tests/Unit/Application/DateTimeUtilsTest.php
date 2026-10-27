<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use kissj\Application\DateTimeUtils;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    public function testGetDayKeepsCalendarDateOfValueAtBerlinMidnight(): void
    {
        $day = DateTimeUtils::getDay(DateTimeUtils::getDateTime('2026-11-14T00:00:00+00:00'));

        self::assertSame('2026-11-14T00:00:00+01:00', $day->format('c'));
        self::assertSame('Europe/Berlin', $day->getTimezone()->getName());
    }

    public function testFormatAtomConvertsToBerlinTime(): void
    {
        $utc = DateTimeUtils::getDateTime('2027-05-19T07:30:00+00:00');

        self::assertSame('2027-05-19T09:30:00+02:00', DateTimeUtils::formatAtom($utc));
    }

    public function testGetDayDropsTimeOfDay(): void
    {
        $day = DateTimeUtils::getDay(DateTimeUtils::getDateTime('2026-07-01 23:30:00'));

        self::assertSame('2026-07-01T00:00:00+02:00', $day->format('c'));
    }
}
