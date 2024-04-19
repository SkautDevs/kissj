<?php

namespace kissj\Orm;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use LeanMapper\Entity;

/**
 * @property DateTimeInterface|null $createdAt m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface|null $updatedAt m:passThru(dateFromString|dateToString)
 */
class EntityDatetime extends Entity
{
    public function dateToString(?DateTimeInterface $value): ?string
    {
        return $value?->format(DATE_ATOM);
    }

    public function dateFromString(DateTimeInterface|string|null $value): ?DateTimeInterface
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        return DateTimeUtils::getDateTime($value);
    }

    public static function setUpdatedAtBeforePersist(EntityDatetime $entity): void
    {
        $entity->updatedAt = DateTimeUtils::getDateTime();
    }

    public static function setCreatedAtBeforeCreate(EntityDatetime $entity): void
    {
        $entity->createdAt = DateTimeUtils::getDateTime();
    }
}
