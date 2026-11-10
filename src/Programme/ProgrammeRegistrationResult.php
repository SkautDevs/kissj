<?php

declare(strict_types=1);

namespace kissj\Programme;

readonly class ProgrammeRegistrationResult
{
    private function __construct(
        public string $translationKey,
        public ?ProgrammeRegistrationError $error,
    ) {
    }

    public static function success(string $translationKey): self
    {
        return new self($translationKey, null);
    }

    public static function failure(ProgrammeRegistrationError $error): self
    {
        return new self($error->flashKey(), $error);
    }
}
