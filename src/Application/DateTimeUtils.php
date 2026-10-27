<?php

declare(strict_types=1);

namespace kissj\Application;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTimeUtils
{
    public const string TIMEZONE = 'Europe/Berlin';

    public static function getDateTime(string $datetime = 'now'): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, new DateTimeZone(self::TIMEZONE));
    }

    public static function formatAtom(DateTimeInterface $value): string
    {
        return DateTimeImmutable::createFromInterface($value)
            ->setTimezone(new DateTimeZone(self::TIMEZONE))
            ->format(DATE_ATOM);
    }

    public static function getDay(DateTimeInterface $value): DateTimeImmutable
    {
        return self::getDateTime($value->format('Y-m-d'));
    }
}
