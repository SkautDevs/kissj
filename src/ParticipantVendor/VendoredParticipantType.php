<?php

namespace kissj\ParticipantVendor;

class VendoredParticipantType
{
    public ?string $role;
    public ?string $name;
    public ?string $surname;
    public ?string $birthdate;
    public ?string $leaderName;
    public ?string $leaderSurname;
    public ?string $leaderContact;
    public ?string $nickname;
    public ?string $psychicalHealth; // nullable string
    public ?string $physicalHealth;  // nullable string
    public ?string $medicaments;     // nullable string

    public function __construct(
        ?string $role,
        ?string $name,
        ?string $surname,
        ?string $birthdate,
        ?string $nickname = null,
        ?string $leaderName = null,
        ?string $leaderSurname = null,
        ?string $leaderContact = null,
        ?string $psychicalHealth = null,
        ?string $physicalHealth = null,
        ?string $medicaments = null
    ) {
        $this->role = $role;
        $this->nickname = $nickname;
        $this->name = $name;
        $this->surname = $surname;
        $this->birthdate = $birthdate;
        $this->leaderName = $leaderName;
        $this->leaderSurname = $leaderSurname;
        $this->leaderContact = $leaderContact;
        $this->psychicalHealth = $psychicalHealth;
        $this->physicalHealth = $physicalHealth;
        $this->medicaments = $medicaments;
    }

}