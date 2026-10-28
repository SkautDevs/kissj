<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiPairingApprovedDTO
{
    public function __construct(
        public string $status,
        public string $sessionToken,
        public string $expiresAt,
    ) {
    }
}
