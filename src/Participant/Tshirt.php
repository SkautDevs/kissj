<?php

declare(strict_types=1);

namespace kissj\Participant;

readonly class Tshirt
{
    private const string DELIMITER = '-';

    public function __construct(
        public ?string $shape,
        public ?string $size,
    ) {
    }

    public static function fromStored(?string $stored): self
    {
        if ($stored === null || $stored === '') {
            return new self(null, null);
        }

        $parts = explode(self::DELIMITER, $stored, 2);

        return new self($parts[0], $parts[1] ?? null);
    }

    public function toStored(): string
    {
        return ($this->shape ?? '') . self::DELIMITER . ($this->size ?? '');
    }
}
