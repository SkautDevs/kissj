<?php

declare(strict_types=1);

namespace kissj\Entry;

readonly class EntryParticipant
{
    public function __construct(
        public int $id,
        public string $firstname,
        public string $lastname,
        public string $nickname,
        public ?string $patrolName,
        public string $tieCode,
        public \DateTimeInterface $birthDate,
        public bool $sfh,
    ) {
    }
}
