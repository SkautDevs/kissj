<?php

declare(strict_types=1);

namespace kissj\Programme;

use LogicException;

readonly class ProgrammeRegistrationResult
{
    private function __construct(
        private ?string $successFlashKey,
        public ?ProgrammeRegistrationError $error,
    ) {
    }

    public static function success(?string $flashKey = null): self
    {
        return new self($flashKey, null);
    }

    public static function failure(ProgrammeRegistrationError $error): self
    {
        return new self(null, $error);
    }

    public function flashKey(): string
    {
        return $this->error?->flashKey()
            ?? $this->successFlashKey
            ?? throw new LogicException('Programme result has no flash text');
    }
}
