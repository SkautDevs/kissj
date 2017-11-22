<?php

namespace kissj\Participant\Patrol;

use LeanMapper\Entity;

/**
 * @property int          $id
 * @property string       $firstName
 * @property string       $lastName
 * @property string       $allergies
 * @property \DateTime    $birthDate m:passThru(fromString|toString)
 * @property string       $birthPlace
 * @property string       $country
 * @property string       $gender
 * @property string       $permanentResidence
 * @property string       $scoutUnit
 * @property string       $telephoneNumber
 * @property string       $email
 * @property string       $foodPreferences
 * @property string       $cardPassportNumber
 * @property string       $notes
 *
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