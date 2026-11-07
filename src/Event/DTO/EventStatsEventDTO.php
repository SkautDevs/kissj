<?php

declare(strict_types=1);

namespace kissj\Event\DTO;

readonly class EventStatsEventDTO
{
    public function __construct(
        public string $name,
    ) {
    }
}
