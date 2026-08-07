<?php

declare(strict_types=1);

namespace Tests\Unit\Event\EventType;

use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Participant\Ist\Ist;
use PHPUnit\Framework\TestCase;

class EventTypePriceTest extends TestCase
{
    public function testNavigamusIstPrice(): void
    {
        self::assertSame(900, (new EventTypeNavigamus())->getPrice(new Ist()));
    }

    public function testAquaIstPrice(): void
    {
        self::assertSame(60, (new EventTypeAqua())->getPrice(new Ist()));
    }
}
