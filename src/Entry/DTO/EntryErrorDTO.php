<?php

declare(strict_types=1);

namespace kissj\Entry\DTO;

use kissj\Entry\EntryStatus;

readonly class EntryErrorDTO
{
    public function __construct(
        public EntryStatus $status,
        public string $reason,
    ) {
    }
}
