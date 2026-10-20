<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use PHPUnit\Framework\TestCase;

class EventTypeDefaultTest extends TestCase
{
    public function testDefaultUsesGenericFaviconAndThemeColors(): void
    {
        $eventType = new EventTypeDefault();
        self::assertSame('favicon-16x16.png', $eventType->getFaviconNameWithoutLeadingSlash());
        self::assertNull($eventType->getAppleTouchIconNameWithoutLeadingSlash());
        self::assertSame('#e6ebed', $eventType->getThemeColorLight());
        self::assertSame('#212121', $eventType->getThemeColorDark());
    }
}
