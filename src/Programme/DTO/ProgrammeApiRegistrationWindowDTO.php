<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiRegistrationWindowDTO
{
    public function __construct(
        public ?string $start,
        public ?string $end,
    ) {
    }
}
