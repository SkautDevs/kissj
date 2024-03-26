<?php

declare(strict_types=1);

namespace kissj\Logging\Monolog;

use kissj\Event\Event;
use Monolog\Processor\ProcessorInterface;

readonly class EventContextProcessor implements ProcessorInterface
{
    public function __construct(
        private ?Event $event,
    ) {
    }

    /**
     * @param array<array<mixed>> $record
     * @return array<mixed>
     */
    public function __invoke(array $record): array
    {
        $event = $this->event;

        $record['context']['event'] = [
            'id' => $event?->id,
            'slug' => $event?->slug,
            'readableName' => $event?->readableName,
        ];

        return $record;
    }
}
