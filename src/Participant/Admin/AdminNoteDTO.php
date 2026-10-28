<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

readonly class AdminNoteDTO
{
    public function __construct(
        public string $adminNote,
    ) {
    }
}
