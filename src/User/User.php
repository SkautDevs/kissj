<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property \DateTime $birthDate m:passThru(fromString|toString)
 * @property string $phone
 * @property string $country
 * @property string $group
 */
class User extends Entity {

	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}

	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}