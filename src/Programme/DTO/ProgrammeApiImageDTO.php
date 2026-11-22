<?php

declare(strict_types=1);

namespace kissj\Programme\DTO;

readonly class ProgrammeApiImageDTO
{
    public function __construct(
        public string $url,
        public string $thumbUrl,
        public string $alt,
    ) {
    }
}
