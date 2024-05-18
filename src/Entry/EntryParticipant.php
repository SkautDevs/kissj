<?php

declare(strict_types=1);

namespace kissj\Entry;

class EntryParticipant
{
    /** @var EntryParticipant[] */
    public array $participants = [];

    public function __construct(
        readonly public int $id,
        readonly public string $firstname,
        readonly public string $lastname,
        readonly public string $nickname,
        readonly public ?string $patrolName,
        readonly public string $tieCode,
        readonly public \DateTimeInterface $birthDate,
        readonly public EntryStatus $entryStatus,
        readonly public bool $sfh,
    ) {
    }
}
