<?php

namespace kissj\Participant\Patrol;

use kissj\Participant\Participant;
use kissj\User\User;


/**
 * @property int            $id
 * @property string|null    $firstName
 * @property string|null    $lastName
 * @property string|null    $allergies
 * @property \DateTime|null $birthDate m:passThru(dateFromString|dateToString)
 * @property string|null    $birthPlace
 * @property string|null    $country
 * @property string|null    $gender
 * @property string|null    $permanentResidence
 * @property string|null    $scoutUnit
 * @property string|null    $telephoneNumber
 * @property string|null    $email
 * @property string|null    $foodPreferences
 * @property string|null    $cardPassportNumber
 * @property string|null    $notes
 *
 * @property string|null    $patrolName
 *
 * @property User           $user      m:hasOne
 */
class PatrolLeader extends Participant {

}
