<?php

namespace kissj\User;

use LeanMapper\Entity;

/**
 * Class User
 * @property int       $id
 * @property string    $token
 * @property User      $user    m:hasOne
 * @property boolean   $used
 * @property \DateTime $created m:passThru(dateFromString|dateToString)
 */
class LoginToken extends Entity {
    public function dateToString(?\DateTime $val): ?string {
        if ($val === null) {
            return null;
        }

        return $val->format(DATE_ATOM);
    }

    public function dateFromString(?string $val): ?\DateTime {
        if (empty($val)) {
            return null;
        }

        return new \DateTime($val);
    }
}
