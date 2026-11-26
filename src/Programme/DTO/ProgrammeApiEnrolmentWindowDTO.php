<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiEnrolmentWindowDTO
{
    public function __construct(
        public ?string $start,
        public ?string $end,
    ) {
    }
}
