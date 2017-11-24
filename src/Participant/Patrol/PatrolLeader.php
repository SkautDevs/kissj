<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;
use LeanMapper\Entity;

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
 * @property boolean        $finished
 * @property User           $user m:hasOne
 */
class PatrolLeader extends Entity {
	
	public function dateToString(?\DateTime $val): ?string {
		if (is_null($val)) {
			return null;
		} else {
			return $val->format(DATE_ISO8601);
		}
	}
	
	public function dateFromString(?string $val): ?\DateTime {
		if (empty($val)) {
			return null;
		} else {
			return new \DateTime($val);
		}
	}
}