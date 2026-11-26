<?php

declare(strict_types=1);

namespace kissj\Device\DTO;

readonly class DeviceApiPairingStatusDTO
{
    public function __construct(
        public string $status,
    ) {
    }
}
