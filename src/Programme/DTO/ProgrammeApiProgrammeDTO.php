<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiProgrammeDTO
{
    /**
     * @param list<string> $targetRoles
     * @param list<ProgrammeApiImageDTO> $images
     */
    public function __construct(
        public int $id,
        public string $name,
        public int $sectionId,
        public string $description,
        public string $place,
        public ?int $placeId,
        public string $start,
        public string $end,
        public bool $isAutoenrolled,
        public array $targetRoles,
        public array $images,
    ) {
    }
}
