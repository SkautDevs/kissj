<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeInterface;
use kissj\Event\Event;
use kissj\Orm\EntityDatetime;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Place\ProgrammePlace;

/**
 * @property int                    $id
 * @property string                 $name
 * @property string                 $description
 * @property DateTimeInterface      $start m:passThru(dateFromString|dateToString)
 * @property DateTimeInterface      $end m:passThru(dateFromString|dateToString)
 * @property int                    $capacity
 * @property bool                   $isAutoenrolled
 * @property array                  $roles m:passThru(rolesFromString|rolesToString)
 * @phpstan-property list<ParticipantRole> $roles
 * @property ProgrammeSection       $section m:hasOne(section_id)
 * @property ProgrammePlace|null    $programmePlace m:hasOne(place_id)
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

    /**
     * @return list<ParticipantRole>
     */
    protected function rolesFromString(string $roles): array
    {
        return array_map(
            ParticipantRole::from(...),
            array_values(array_filter(
                explode(self::ROLES_DELIMITER, $roles),
                static fn (string $value): bool => $value !== '',
            )),
        );
    }

    /**
     * @param list<ParticipantRole> $roles
     */
    protected function rolesToString(array $roles): string
    {
        return self::encodeRoleValues(array_map(static fn (ParticipantRole $role): string => $role->value, $roles));
    }

    public function isForRole(ParticipantRole $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function isExclusiveWith(self $other): bool
    {
        return $this->section->id === $other->section->id
            && $this->section->isExclusive
            && $this->overlapsWith($other);
    }

    public function getPlaceName(): string
    {
        return $this->programmePlace === null ? '' : $this->programmePlace->name;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
