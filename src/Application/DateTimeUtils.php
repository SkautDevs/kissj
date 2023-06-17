<?php

declare(strict_types=1);

namespace kissj\Application;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeUtils
{
    public static function getDateTime(string $datetime = 'now'): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, new DateTimeZone('Europe/Berlin'));
    }
}
