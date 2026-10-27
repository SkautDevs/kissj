<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiProgrammeDTO
{
    /**
     * @param list<string> $targetRoles
     */
    public function __construct(
        public int $id,
        public string $name,
        public int $sectionId,
        public string $description,
        public string $place,
        public string $start,
        public string $end,
        public bool $isPreregistered,
        public array $targetRoles,
    ) {
    }
}
