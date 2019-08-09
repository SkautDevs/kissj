<?php

namespace kissj\Participants\Ist;

use kissj\Participants\Participant;
use kissj\User\User;


/**
 * @property int            $id
 * @property string|null    $firstName          ,
 * @property string|null    $lastName           ,
 * @property string|null    $nickname           ,
 * @property \DateTime|null $birthDate          m:passThru(dateFromString|dateToString)
 * @property string|null    $gender             ,
 * @property string|null    $permanentResidence ,
 * @property string|null    $email              ,
 * @property string|null    $legalRepresestative,
 * @property string|null    $scarf              ,
 * @property string|null    $notes
 *
 * @property User           $user               m:hasOne
 */
class Ist extends Participant {

}
