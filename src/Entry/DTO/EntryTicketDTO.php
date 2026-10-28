<?php

declare(strict_types=1);

namespace kissj\Entry\DTO;

use kissj\Entry\EntryStatus;

readonly class EntryTicketDTO
{
    public function __construct(
        public string $eventName,
        public string $fullName,
        public ?string $email,
        public ?int $ageAtEventStart,
        public ?string $tshirtShape,
        public ?string $tshirtSize,
        public EntryStatus $status,
        public string $entryDateTime,
    ) {
    }
}
