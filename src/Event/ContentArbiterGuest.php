<?php

namespace kissj\Event;

class ContentArbiterGuest extends AbstractContentArbiter {
    public bool $address = false;
    public bool $birthDate = false;
    public bool $health = false;
    public bool $phone = true;
    public bool $email = true;
    public bool $arrivalDate = true;
    public bool $departureDate = true;
}
