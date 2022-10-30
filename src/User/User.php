<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int        $id
 * @property string     $email
 * @property UserRole   $role   m:passThru(roleFromString|roleToString)
 * @property Event      $event  m:hasOne
 * @property UserStatus $status m:passThru(statusFromString|statusToString)
 */
class User extends EntityDateTime
{
    public function initDefaults(): void
    {
        $this->status = UserStatus::WithoutRole;
    }

    public function statusFromString(string $status): UserStatus
    {
        return UserStatus::from($status);
    }

    public function statusToString(UserStatus $status): string
    {
        return $status->value;
    }

    public function roleFromString(string $status): UserRole
    {
        return UserRole::from($status);
    }

    public function roleToString(UserRole $role): string
    {
        return $role->value;
    }
    
    public function isAdmin(): bool
    {
        return in_array($this->role, UserRole::adminRoles(), true);
    }
}
