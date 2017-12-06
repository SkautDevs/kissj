<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int $id
 * @property string $token
 * @property User $user m:hasOne
 * @property boolean $used
 * @property \DateTime $created m:passThru(dateFromString|dateToString)
 */
class LoginToken extends Entity {

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