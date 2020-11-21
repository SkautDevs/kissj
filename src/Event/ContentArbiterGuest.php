<?php

namespace kissj\Event;

class ContentArbiterGuest extends AbstractContentArbiter {
    public bool $address = false;
    public bool $phone = true;
    public bool $email = true;
    public bool $scarf = false;
    public bool $swimming = false;
    public bool $tshirt = false;
    public bool $arrivalDate = true;
    public bool $departueDate = true;
}
