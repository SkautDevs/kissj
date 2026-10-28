<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiOkDTO
{
    public function __construct(
        public bool $ok,
    ) {
    }
}
