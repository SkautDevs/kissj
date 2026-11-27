<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeImmutable;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Place\ProgrammePlace;

readonly class ProgrammeData
{
    /**
     * @param list<ParticipantRole> $roles
     */
    public function __construct(
        public string $name,
        public string $description,
        public DateTimeImmutable $start,
        public DateTimeImmutable $end,
        public int $capacity,
        public bool $isAutoenrolled,
        public array $roles,
        public ProgrammeSection $section,
        public ?ProgrammePlace $place,
    ) {
    }
}
