<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int    $id
 * @property string $email
 * @property Role   $role m:hasOne
 */
class User extends Entity {
	
	public function dateToString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}
	
	public function dateFromString(string $val): string {
		return new \DateTime($val);
	}
}