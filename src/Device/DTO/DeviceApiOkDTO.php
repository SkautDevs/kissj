<?php

declare(strict_types=1);

namespace kissj\Device\DTO;

readonly class DeviceApiOkDTO
{
    public function __construct(
        public bool $ok,
    ) {
    }
}
