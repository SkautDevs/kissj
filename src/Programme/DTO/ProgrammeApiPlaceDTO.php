<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiPlaceDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?float $lat,
        public ?float $lng,
    ) {
    }
}
