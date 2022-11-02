<?php

declare(strict_types=1);

namespace kissj\Logging\Monolog;

use kissj\Event\Event;
use Monolog\Processor\ProcessorInterface;

final class EventContextProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ?Event $event,
    ) {
    }

    /**
     * @param array<mixed> $records
     * @return array<mixed>
     */
    public function __invoke(array $records): array
    {
        $event = $this->event;

        $records['context']['event'] = [
            'id' => $event?->id,
            'slug' => $event?->slug,
            'readableName' => $event?->readableName,
        ];

        return $records;
    }
}
