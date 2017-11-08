<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property \DateTime $birthDate m:passThru(fromString|toString)
 * @property string $allergies
 * @property string $phoneNumber
 * @property string $country
 */
class PatrolLeader extends Entity {

	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}

	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}