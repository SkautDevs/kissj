<?php

declare(strict_types=1);

namespace kissj\ParticipantVendor;

readonly class VendoredParticipantResponseDTO
{
    public function __construct(
        public string $participant,
    ) {
    }
}
