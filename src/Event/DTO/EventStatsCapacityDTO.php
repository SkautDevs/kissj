<?php

declare(strict_types=1);

namespace kissj\Event\DTO;

readonly class EventStatsCapacityDTO
{
    public function __construct(
        public int $used,
        public ?int $max,
    ) {
    }
}
