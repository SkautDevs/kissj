<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiPairingStatusDTO
{
    public function __construct(
        public string $status,
    ) {
    }
}
