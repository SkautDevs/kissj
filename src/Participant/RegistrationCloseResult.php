<?php

declare(strict_types=1);

namespace kissj\Participant;

readonly class RegistrationCloseResult
{
    /**
     * @param array<array{key: string, params: array<string, string>}> $warnings
     */
    public function __construct(
        public bool $isValid,
        public array $warnings = [],
    ) {
    }

    public static function startChecking(): self
    {
        return new self(true);
    }

    /**
     * @param array<string, string> $params
     */
    public function withWarning(string $key, array $params = []): self
    {
        return new self(
            false,
            [...$this->warnings, ['key' => $key, 'params' => $params]],
        );
    }
}
