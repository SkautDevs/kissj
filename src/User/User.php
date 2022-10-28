<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int        $id
 * @property string     $email
 * @property string     $role   m:enum(self::ROLE_*) // TODO change to roles "admins or participant"
 * @property Event      $event  m:hasOne
 * @property UserStatus $status m:passThru(statusFromString|statusToString)
 */
class User extends EntityDateTime
{
    public const ROLE_IST = 'ist';
    public const ROLE_PATROL_LEADER = 'pl';
    public const ROLE_PATROL_PARTICIPANT = 'pp';
    public const ROLE_GUEST = 'guest';
    public const ROLE_WITHOUT_ROLE = 'withoutRole';
    public const ROLE_TROOP_LEADER = 'tl';
    public const ROLE_TROOP_PARTICIPANT = 'tp';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CONTINGENT_ADMIN_CS = 'contingentAdminCs';
    public const ROLE_CONTINGENT_ADMIN_SK = 'contingentAdminSk';
    public const ROLE_CONTINGENT_ADMIN_PL = 'contingentAdminPl';
    public const ROLE_CONTINGENT_ADMIN_HU = 'contingentAdminHu';
    public const ROLE_CONTINGENT_ADMIN_EU = 'contingentAdminEu';
    public const ROLE_CONTINGENT_ADMIN_RO = 'contingentAdminRo';

    public const ROLES = [
        self::ROLE_IST,
        self::ROLE_PATROL_LEADER,
        self::ROLE_PATROL_PARTICIPANT,
        self::ROLE_GUEST,
        self::ROLE_WITHOUT_ROLE,
        self::ROLE_TROOP_LEADER,
        self::ROLE_TROOP_PARTICIPANT,
        self::ROLE_ADMIN,
        self::ROLE_CONTINGENT_ADMIN_CS,
        self::ROLE_CONTINGENT_ADMIN_SK,
        self::ROLE_CONTINGENT_ADMIN_PL,
        self::ROLE_CONTINGENT_ADMIN_HU,
        self::ROLE_CONTINGENT_ADMIN_EU,
        self::ROLE_CONTINGENT_ADMIN_RO,
    ];

    public const ADMIN_ROLES_ONLY = [
        self::ROLE_ADMIN,
        self::ROLE_CONTINGENT_ADMIN_CS,
        self::ROLE_CONTINGENT_ADMIN_SK,
        self::ROLE_CONTINGENT_ADMIN_PL,
        self::ROLE_CONTINGENT_ADMIN_HU,
        self::ROLE_CONTINGENT_ADMIN_EU,
        self::ROLE_CONTINGENT_ADMIN_RO,
    ];

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
}
