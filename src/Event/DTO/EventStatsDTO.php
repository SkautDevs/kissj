<?php

declare(strict_types=1);

namespace kissj\Event\DTO;

readonly class EventStatsDTO
{
    /**
     * @param array<string, EventStatsCapacityDTO> $capacity
     */
    public function __construct(
        public EventStatsEventDTO $event,
        public array $capacity,
        public int $entered,
        public string $generatedAt,
    ) {
    }
}
