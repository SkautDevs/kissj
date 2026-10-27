<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeInterface;
use kissj\Event\Event;
use kissj\Orm\EntityDatetime;
use kissj\Participant\ParticipantRole;

/**
 * @property int                    $id
 * @property string                 $name
 * @property string                 $description
 * @property string                 $place
 * @property string|null            $imageFilename
 * @property DateTimeInterface      $start m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface      $end m:passThru(dateFromString|dateToString)
 * @property int                    $capacity
 * @property bool                   $isPreregistered
 * @property ProgrammeSection       $section m:hasOne(section_id)
 * @property DateTimeInterface|null $deletedAt m:passThru(dateFromString|dateToString)
 * @property Event                  $event m:hasOne
 */
class Programme extends EntityDatetime
{
    private const string ROLES_DELIMITER = ',';

    public function overlapsWith(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function isExclusiveWith(self $other): bool
    {
        return $this->section->id === $other->section->id
            && $this->section->isExclusive
            && $this->overlapsWith($other);
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * @return list<ParticipantRole>
     */
    public function getTargetRoles(): array
    {
        if ($this->row->hasColumn('roles') === false) {
            return [];
        }

        /** @var string $rolesFromDb */
        $rolesFromDb = $this->row->roles;
        $roles = [];
        foreach (explode(self::ROLES_DELIMITER, trim($rolesFromDb, self::ROLES_DELIMITER)) as $roleValue) {
            if ($roleValue === '') {
                continue;
            }
            $roles[] = ParticipantRole::from($roleValue);
        }

        return $roles;
    }

    /**
     * @param list<ParticipantRole> $roles
     */
    public function setTargetRoles(array $roles): void
    {
        $this->row->roles = self::encodeRoleValues(array_map(
            static fn (ParticipantRole $role): string => $role->value,
            $roles,
        ));
    }

    /**
     * @param list<string> $roleValues
     */
    public static function encodeRoleValues(array $roleValues): string
    {
        if ($roleValues === []) {
            return '';
        }

        return self::ROLES_DELIMITER . implode(self::ROLES_DELIMITER, $roleValues) . self::ROLES_DELIMITER;
    }

    public function isForRole(ParticipantRole $role): bool
    {
        return in_array($role, $this->getTargetRoles(), true);
    }
}
