<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int           $id
 * @property string        $email
 * @property int|null      $skautisId
 * @property bool|null     $skautisHasMembership
 * @property UserRole      $role   m:passThru(roleFromString|roleToString)
 * @property Event         $event  m:hasOne
 * @property UserStatus    $status m:passThru(statusFromString|statusToString)
 * @property UserLoginType $loginType m:passThru(loginTypeFromString|loginTypeToString)
 */
class User extends EntityDateTime
{
    public function initDefaults(): void
    {
        parent::initDefaults();
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

    public function loginTypeFromString(string $status): UserLoginType
    {
        return UserLoginType::from($status);
    }

    public function loginTypeToString(UserLoginType $userLogin): string
    {
        return $userLogin->value;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, UserRole::adminRoles(), true);
    }
}
