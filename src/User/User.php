<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int    $id
 * @property string $email
 * @property string $role
 * @property Event  $event  m:hasOne
 * @property string $status m:enum(self::STATUS_*) m:default('withoutRole')
 */
class User extends EntityDateTime {
    public const STATUS_WITHOUT_ROLE = 'withoutRole';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';

    public const ROLE_IST = 'ist';
    public const ROLE_PATROL_LEADER = 'pl';
    public const ROLE_GUEST = 'guest';

    public const ROLE_ADMIN = 'admin';
}
