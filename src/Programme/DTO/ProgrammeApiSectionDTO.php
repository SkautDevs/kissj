<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiSectionDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $subtitle,
    ) {
    }
}
