<?php

declare(strict_types=1);

namespace kissj\Entry;

class EntryParticipant
{
    /** @var EntryParticipant[] */
    public array $participants = [];

    public function __construct(
        public readonly int $id,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $nickname,
        public readonly ?string $patrolName,
        public readonly string $tieCode,
        public readonly \DateTimeInterface $birthDate,
        public readonly EntryStatus $entryStatus,
        public readonly bool $sfh,
    ) {
    }
}
