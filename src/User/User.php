<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int    $id
 * @property string $email
 * @property string $role m:enum(self::ROLE_*)
 */
class User extends Entity {
	
	const ROLE_PL = 'patrol-leader';
	const ROLE_IST = 'ist';
	const ROLE_G = 'guest';
	const ROLE_S = 'staff';
	const ROLE_T = 'team';
	const ROLE_EC = 'event-chief';
	const ROLE_CC = 'contingent-chief';
	
	public function toString(\DateTime $val): string {
		return $val->format(DATE_ISO8601);
	}
	
	// TODO rename more verbally (getDateFromString? dateFromString?)
	public function fromString(string $val): string {
		return new \DateTime($val);
	}
}