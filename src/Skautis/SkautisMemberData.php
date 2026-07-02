<?php

declare(strict_types=1);

namespace kissj\Skautis;

use DateTimeImmutable;
use kissj\Participant\Country;
use kissj\Participant\Gender;

readonly class SkautisMemberData
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $nickName,
        public DateTimeImmutable $birthday,
        private string $street,
        private string $city,
        private string $postcode,
        public Country $country,
        public Gender $gender,
    ) {
    }

    public function getFullName(): string
    {
        return $this->lastName . ' ' . $this->firstName
            . ($this->nickName !== '' ? ' (' . $this->nickName . ')' : '');
    }

    public function getPermanentResidence(): string
    {
        $fields = [$this->street, $this->city, $this->postcode];
        $filterEmpty = static fn (string $field): bool => $field !== '';

        return implode(', ', array_filter($fields, $filterEmpty));
    }

}
