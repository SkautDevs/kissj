<?php

namespace kissj\Orm;

use DateTime;
use LeanMapper\Entity;

/**
 * @property DateTime $createdAt m:passThru(dateFromString|dateToString)
 * @property DateTime $updatedAt m:passThru(dateFromString|dateToString)
 */
class EntityDatetime extends Entity
{
    public function dateToString(?DateTime $val): ?string
    {
        if ($val === null) {
            return null;
        }

        return $val->format(DATE_ATOM);
    }

    public function dateFromString(string|DateTime $val): ?DateTime
    {
        if (empty($val)) {
            return null;
        }

        if ($val instanceof DateTime) {
            return $val;
        }

        return new DateTime($val);
    }

    public static function setUpdatedAtBeforePersist(EntityDatetime $entity): void
    {
        $entity->updatedAt = new DateTime();
    }

    public static function setCreatedAtBeforeCreate(EntityDatetime $entity): void
    {
        $entity->createdAt = new DateTime();
    }
}
