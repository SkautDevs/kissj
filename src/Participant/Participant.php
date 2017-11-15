<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;
use LeanMapper\Entity;

/**
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property string $nationality
 * @property string $gender
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property \DateTime $birthDate m:passThru(fromString|toString)
 * @property string $birthPlace
 * @property string $allergies
 * @property string $foodPreferences
 * @property string $cardPassportNumber
 * @property string $notes
 * @property User $user m:hasOne
 */
class PatrolParticipant extends Entity {

	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}

	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}