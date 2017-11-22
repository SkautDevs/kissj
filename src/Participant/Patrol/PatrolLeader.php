<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;
use LeanMapper\Entity;

/**
 * @property int       $id
 * @property string    $firstName
 * @property string    $lastName
 * @property string    $allergies
 * @property \DateTime $birthDate m:passThru(dateFromString|dateToString)
 * @property string    $birthPlace
 * @property string    $country
 * @property string    $gender
 * @property string    $permanentResidence
 * @property string    $scoutUnit
 * @property string    $telephoneNumber
 * @property string    $email
 * @property string    $foodPreferences
 * @property string    $cardPassportNumber
 * @property string    $notes
 *
 * @property string    $patrolName
 *
 * @property boolean   $finished
 * @property User      $user m:hasOne
 */
class PatrolLeader extends Entity {
	
	public function dateToString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}
	
	public function dateFromString(string $val): string {
		return new \DateTime($val);
	}
}