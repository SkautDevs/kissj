<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiPairingStartDTO
{
    public function __construct(
        public string $deviceCode,
        public string $userCode,
        public string $verificationUrl,
        public string $expiresAt,
        public int $interval,
    ) {
    }
}
