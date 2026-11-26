<?php

declare(strict_types=1);

namespace kissj\Device\DTO;

readonly class DeviceApiPairingStartDTO
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
