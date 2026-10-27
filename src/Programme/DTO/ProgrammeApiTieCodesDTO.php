<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiTieCodesDTO
{
    /**
     * @param list<string> $tieCodes
     */
    public function __construct(
        public array $tieCodes,
    ) {
    }
}
