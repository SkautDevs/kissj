<?php

declare(strict_types=1);

namespace kissj\Entry;

use DateTimeInterface;

enum EntryStatus: string
{
    case ENTRY_STATUS_VALID = 'valid';
    case ENTRY_STATUS_USED = 'used';
    case ENTRY_STATUS_INVALID = 'invalid';

    public static function fromDatetime(?DateTimeInterface $entryDate): self
    {
        return match ($entryDate) {
            null => self::ENTRY_STATUS_VALID,
            default => self::ENTRY_STATUS_USED,
        };
    }
}
