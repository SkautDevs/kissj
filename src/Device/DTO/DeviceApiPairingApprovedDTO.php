<?php

declare(strict_types=1);

namespace kissj\Device\DTO;

readonly class DeviceApiPairingApprovedDTO
{
    public function __construct(
        public string $status,
        public string $sessionToken,
        public string $expiresAt,
    ) {
    }
}
