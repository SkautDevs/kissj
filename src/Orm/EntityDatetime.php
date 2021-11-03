<?php

namespace kissj\Orm;

use DateTime;
use DateTimeInterface;
use LeanMapper\Entity;

/**
 * @property DateTimeInterface $createdAt m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface $updatedAt m:passThru(dateFromString|dateToString)
 */
class EntityDatetime extends Entity
{
    public function dateToString(?DateTimeInterface $val): ?string
    {
        if ($val === null) {
            return null;
        }

        return $val->format(DATE_ATOM);
    }

    public function dateFromString(string|DateTimeInterface $val): ?DateTimeInterface
    {
        if (empty($val)) {
            return null;
        }

        if ($val instanceof DateTimeInterface) {
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
