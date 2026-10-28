<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiErrorDTO
{
    public function __construct(
        public string $error,
    ) {
    }
}
