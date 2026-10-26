<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\EventTypeDefault;
use PHPUnit\Framework\TestCase;

class EventTypeDefaultTest extends TestCase
{
    public function testOffersOnlyCzech(): void
    {
        self::assertSame(['cs' => '🇨🇿 Česky'], (new EventTypeDefault())->getLanguages());
    }

    public function testAllowsOnlySkautisLogin(): void
    {
        $eventType = new EventTypeDefault();

        self::assertTrue($eventType->isLoginSkautisAllowed());
        self::assertFalse($eventType->isLoginEmailAllowed());
    }

    public function testEnforcesActiveSkautisMembership(): void
    {
        self::assertTrue((new EventTypeDefault())->enforceActiveSkautisMembership());
    }
}
