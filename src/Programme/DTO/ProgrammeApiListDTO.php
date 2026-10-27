<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiListDTO
{
    /**
     * @param list<ProgrammeApiSectionDTO> $sections
     * @param list<ProgrammeApiProgrammeDTO> $programmes
     */
    public function __construct(
        public array $sections,
        public array $programmes,
    ) {
    }
}
