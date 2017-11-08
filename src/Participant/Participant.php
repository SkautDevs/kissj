<?php

namespace kissj\Participant;

use LeanMapper\Entity;

/**
 * Class User
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property \DateTime $birthDate m:passThru(fromString|toString)
 * @property string $allergies
 * @property PatrolLeader $patrolLeader m:hasOne
 */
class Participant extends Entity {

	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}

	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}