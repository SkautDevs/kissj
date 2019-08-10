<?php

namespace kissj\Participant;

use kissj\Orm\EntityDatetime;
use kissj\User\User;

/**
 * @property int         $id
 * @property User|null   $user      m:belongsToOne
 * @property string|null $firstName
 * @property string|null $lastName
 * @property string|null $nickname
 * @property string|null $permanentResidence
 * @property string|null $telephoneNumber
 * @property string|null $gender
 * @property string|null $country
 * @property string|null $email
 * @property string|null $scoutUnit
 * @property string|null $birthDate m:passThru(dateFromString|dateToString)
 * @property string|null $birthPlace
 * @property string|null $healthProblems
 * @property string|null $foodPreferences
 * @property string|null $idNumber
 * @property string|null $scarf
 * @property string|null $tshirt
 * @property string|null $notes
 */
class Participant extends EntityDatetime {

}
