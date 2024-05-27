<?php

namespace kissj\ParticipantVendor;

class VendoredParticipantType
{
    public function __construct(
        public ?string $role,
        public ?string $name,
        public ?string $surname,
        public ?string $birthdate,
        public ?string $nickname = null,
        public ?string $leaderName = null,
        public ?string $leaderSurname = null,
        public ?string $leaderContact = null,
        public ?string $psychicalHealth = null,
        public ?string $physicalHealth = null,
        public ?string $medicaments = null
    ) {
    }
}
