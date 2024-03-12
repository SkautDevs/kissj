<?php

declare(strict_types=1);

namespace kissj\Skautis;

use DateTimeImmutable;

readonly class SkautisUserData
{
    public function __construct(
        public int $skautisId,
        public string $skautisUserName,
        public int $skautisIdPerson,
        public string $firstName,
        public string $lastName,
        public string $nickName,
        public DateTimeImmutable $birthday,
        public string $email,
        public string $phone,
        public string $street,
        public string $city,
        public string $postCode,
        public bool $hasMembership,
        public string $unitName,
    ) {
    }

    public function getPermanentResidence(): string
    {
        if ($this->street === '' && $this->city === '' &&  $this->postCode === '') {
            return '';
        }

        return $this->street . ', ' . $this->city . ', ' . $this->postCode;
    }
}
