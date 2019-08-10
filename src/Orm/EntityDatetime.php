<?php

namespace kissj\Orm;

use LeanMapper\Entity;

/**
 * @property string|null $createdAt m:passThru(dateFromString|dateToString)
 * @property string|null $updatedAt m:passThru(dateFromString|dateToString)
 */
class EntityDatetime extends Entity {
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

    public function setUpdatedAtBeforePersist(EntityDatetime $entity): void {
        $entity->updatedAt = new \DateTime();
    }

    public function setCreatedAtBeforeCreate(EntityDatetime $entity): void {
        $entity->createdAt = new \DateTime();
    }
}
