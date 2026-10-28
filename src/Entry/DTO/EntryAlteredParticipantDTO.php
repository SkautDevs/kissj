<?php

declare(strict_types=1);

namespace kissj\Entry\DTO;

use kissj\Entry\EntryStatus;

readonly class EntryAlteredParticipantDTO
{
    public function __construct(
        public EntryStatus $status,
        public int $alteredParticipantId,
    ) {
    }
}
