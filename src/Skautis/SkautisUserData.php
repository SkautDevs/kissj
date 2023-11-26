<?php

declare(strict_types=1);

namespace kissj\Skautis;

use DateTimeImmutable;

class SkautisUserData
{
    public function __construct(
        public readonly int $skautisId,
        public readonly string $skautisUserName,
        public readonly int $skautisIdPerson,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $nickName,
        public readonly DateTimeImmutable $birthday,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $street,
        public readonly string $city,
        public readonly string $postCode,
        public readonly bool $hasMembership,
        public readonly string $unitName,
    ) {
    }
    
    public function getPermanentResidence(): string
    {
        return $this->street . ', ' . $this->city . ', ' . $this->postCode;
    }
}
