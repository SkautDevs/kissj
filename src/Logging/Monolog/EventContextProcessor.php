<?php

declare(strict_types=1);

namespace kissj\Logging\Monolog;

use kissj\Event\Event;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

readonly class EventContextProcessor implements ProcessorInterface
{
    public function __construct(
        private ?Event $event,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $event = $this->event;
        $record = $record->with(context: [
            'event' => [
                'id' => $event?->id,
                'slug' => $event?->slug,
                'readableName' => $event?->readableName,
            ],
        ]);

        return $record;
    }
}
