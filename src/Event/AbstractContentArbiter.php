<?php

namespace kissj\Event;

abstract class AbstractContentArbiter
{
    public bool $contingent = false;
    public bool $patrolName = false;
    public bool $firstName = true;
    public bool $lastName = true;
    public bool $nickname = true;
    public bool $address = true;
    public bool $phone = false;
    public bool $gender = true;
    public bool $country = false;
    public bool $email = false;
    public bool $unit = false;
    public bool $languages = false;
    public bool $birthDate = true;
    public bool $birthPlace = false;
    public bool $health = true;
    public bool $food = false;
    public bool $idNumber = false;
    public bool $scarf = false;
    public bool $swimming = false;
    public bool $tshirt = false;
    public bool $arrivalDate = false;
    public bool $departureDate = false;
    public bool $uploadFile = false;
    public bool $skills = false;
    public bool $preferredPosition = false;
    public bool $driver = false;
    public bool $notes = true;
}
