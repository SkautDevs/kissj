<?php

declare(strict_types=1);

namespace Tests\Unit\Logging\Monolog;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Logging\Monolog\EventContextProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class EventContextProcessorTest extends TestCase
{
    public function testKeepsCallerContextAndAddsEvent(): void
    {
        $event = new Event();
        $event->id = 7;
        $event->slug = 'korbo3026';
        $event->readableName = 'Korbo';

        $record = new LogRecord(
            DateTimeUtils::getDateTime(),
            'KISSJ',
            Level::Warning,
            'Event test',
            ['reason' => 'bad date'],
        );

        $processor = new EventContextProcessor($event);
        $result = $processor($record);

        self::assertSame('bad date', $result->context['reason']);
        self::assertSame(['id' => 7, 'slug' => 'korbo3026', 'readableName' => 'Korbo'], $result->context['event']);
    }

    public function testNullEventYieldsNullFields(): void
    {
        $record = new LogRecord(
            DateTimeUtils::getDateTime(),
            'KISSJ',
            Level::Warning,
            'No event',
            ['reason' => 'test'],
        );

        $processor = new EventContextProcessor(null);
        $result = $processor($record);

        self::assertSame('test', $result->context['reason']);
        self::assertSame([
            'id' => null,
            'slug' => null,
            'readableName' => null,
        ], $result->context['event']);
    }
}
