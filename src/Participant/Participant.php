<?php

namespace kissj\Participant;

use kissj\User\User;
use LeanMapper\Entity;

/**
 * @property int         $id
 * @property string|null $firstName
 * @property string|null $lastName
 * @property string|null $permanentResidence
 * @property string|null $telephoneNumber
 * @property string|null $gender
 * @property string|null $country
 * @property string|null $email
 * @property string|null $scoutUnit
 * @property string|null $birthDate
 * @property string|null $birthPlace
 * @property string|null $allergies
 * @property string|null $foodPreferences
 * @property string|null $cardPassportNumber
 * @property string|null $tshirtSize
 * @property string|null $notes
 *
 * @property User|null   $user m:hasOne
 */
class Participant extends Entity {

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
