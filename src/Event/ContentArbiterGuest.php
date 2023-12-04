<?php

namespace kissj\Event;

class ContentArbiterGuest extends AbstractContentArbiter
{
    public bool $address = false;
    public bool $birthDate = false;
    public bool $health = false;
    public bool $email = true;
    public bool $arrivalDate = true;
    public bool $departureDate = true;
    
    public function __construct()
    {
        parent::__construct();
        $this->phone->allowed = true;
    }
}
