<?php

namespace kissj\Participants;

use LeanMapper\Entity;


// TODO rename class and move elsewhere more appropriate
class Participant extends Entity {

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
