<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Orm\EntityDatetime;

/**
 * @property int    $id
 * @property string $email
 * @property Event  $event  m:hasOne
 * @property string $status m:enum(self::STATUS_*)
 */
class User extends EntityDateTime {
    public const STATUS_WITHOUTROLE = 'withoutRole';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
}
