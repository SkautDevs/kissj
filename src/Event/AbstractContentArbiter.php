<?php

namespace kissj\Event;

class AbstractContentArbiter {
    public bool $firstName = true;
    public bool $lastName = true;
    public bool $nickname = true;
    public bool $address = true;
    public bool $phone = false;
    public bool $gender = true;
    public bool $country = true;
    public bool $email = false;
    public bool $unit = true;
    public bool $languages = false;
    public bool $birthDate = true;
    public bool $birthPlace = true;
    public bool $health = true;
    public bool $food = true;
    public bool $idNumber = false;
    public bool $scarf = true;
    public bool $swimming = true;
    public bool $tshirt = true;
    public bool $arrivalDate = false;
    public bool $departueDate = false;
    public bool $uploadFile = false;
    public bool $notes = true;
}
