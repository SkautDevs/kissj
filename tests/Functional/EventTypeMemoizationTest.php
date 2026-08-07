<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Event\EventType\EventTypeDefault;
use Tests\AppTestCase;

class EventTypeMemoizationTest extends AppTestCase
{
    public function testGetEventTypeReturnsSameInstance(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $this->getSmallTestEvent($eventRepository);

        self::assertInstanceOf(EventTypeDefault::class, $event->getEventType());
        self::assertSame($event->getEventType(), $event->getEventType());
    }
}
