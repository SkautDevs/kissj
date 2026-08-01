<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventTypeDefault;
use PHPUnit\Framework\TestCase;

class SubcampsTest extends TestCase
{
    public function testDefaultEventTypeHasNoSubcamps(): void
    {
        self::assertSame([], (new EventTypeDefault())->getSubcamps());
    }

    public function testCejEventTypeHasThreeSubcamps(): void
    {
        self::assertSame(
            [
                EventTypeCej::SUBCAMP_THEBA,
                EventTypeCej::SUBCAMP_SPARTA,
                EventTypeCej::SUBCAMP_ATHENS,
            ],
            (new EventTypeCej())->getSubcamps(),
        );
    }
}
