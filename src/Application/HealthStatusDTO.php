<?php

declare(strict_types=1);

namespace kissj\Application;

readonly class HealthStatusDTO
{
    public function __construct(
        public string $status,
    ) {
    }
}
