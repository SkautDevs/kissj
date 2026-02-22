<?php

declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterGuest extends AbstractContentArbiter
{
    public function __construct()
    {
        parent::__construct();
        $this->address->allowed = false;
        $this->gender->allowed = false;
        $this->birthDate->allowed = false;
        $this->health->allowed = false;
        $this->psychicalHealth->allowed = false;
        $this->phone->allowed = true;
        $this->email->allowed = true;
        $this->arrivalDate->allowed = true;
        $this->departureDate->allowed = true;
    }
}
