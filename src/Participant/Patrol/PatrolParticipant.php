<?php

namespace kissj\Participant\Patrol;

use LeanMapper\Entity;

/**
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property \DateTime $birthDate m:passThru(fromString|toString)
 * @property string $allergies
 * @property PatrolLeader $patrolLeader m:hasOne
 */
class PatrolParticipant extends Entity {

	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}

	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}