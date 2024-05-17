<?php

declare(strict_types=1);

namespace kissj\Entry;

enum EntryStatus: string
{
    case ENTRY_STATUS_VALID = 'valid';
    case ENTRY_STATUS_USED = 'used';
    case ENTRY_STATUS_INVALID = 'invalid';
}
