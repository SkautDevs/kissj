<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiParticipantProgrammesDTO
{
    /**
     * @param list<ProgrammeApiProgrammeDTO> $programmes
     */
    public function __construct(
        public ProgrammeApiParticipantDTO $participant,
        public array $programmes,
    ) {
    }
}
