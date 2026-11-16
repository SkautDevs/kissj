<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiParticipantDTO
{
    public function __construct(
        public int $id,
        public ?string $nickname,
    ) {
    }
}
