<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int    $id
 * @property string $email
 * @property string $role   m:enum(self::ROLE_*)
 * @property Event  $event  m:hasOne
 * @property string $status m:enum(self::STATUS_*) m:default('withoutRole')
 */
class User extends EntityDateTime {
    public const STATUS_WITHOUT_ROLE = 'withoutRole';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    public const STATUSES = [
        self::STATUS_WITHOUT_ROLE,
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
        self::STATUS_APPROVED,
        self::STATUS_PAID,
    ];

    public const ROLE_IST = 'ist';
    public const ROLE_PATROL_LEADER = 'pl';
    public const ROLE_PATROL_PARTICIPANT = 'pp';
    public const ROLE_GUEST = 'guest';
    public const ROLE_WITHOUT_ROLE = 'withoutRole';
    public const ROLE_FREE_PARTICIPANT = 'fp';

    public const ROLE_ADMIN = 'admin';
    public const ROLES = [
        self::ROLE_IST,
        self::ROLE_PATROL_LEADER,
        self::ROLE_PATROL_PARTICIPANT,
        self::ROLE_GUEST,
        self::ROLE_WITHOUT_ROLE,
        self::ROLE_FREE_PARTICIPANT,
        self::ROLE_ADMIN,
    ];
}
