<?php

declare(strict_types=1);

namespace kissj\Entry\DTO;

use kissj\Entry\EntryStatus;

readonly class EntryAlteredParticipantsDTO
{
    /**
     * @param list<int> $alteredParticipantIds
     */
    public function __construct(
        public EntryStatus $status,
        public array $alteredParticipantIds,
    ) {
    }
}
