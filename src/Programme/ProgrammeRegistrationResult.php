<?php

declare(strict_types=1);

namespace kissj\Programme;

readonly class ProgrammeRegistrationResult
{
    public function __construct(
        public bool $success,
        public string $translationKey,
        public ?int $remainingCapacity = null,
    ) {
    }
}
