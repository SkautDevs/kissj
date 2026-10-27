<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeSectionCountsDTO
{
    public function __construct(
        public int $active,
        public int $deleted,
    ) {
    }
}
