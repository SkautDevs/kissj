<?php

declare(strict_types=1);

namespace kissj\Entry\DTO;

use kissj\Entry\EntryParticipant;

readonly class EntryListDTO
{
    /**
     * @param array<string, array<EntryParticipant>> $roles
     */
    public function __construct(
        public string $eventName,
        public string $eventSlug,
        public array $roles,
    ) {
    }
}
