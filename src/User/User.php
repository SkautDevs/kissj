<?php

namespace kissj\User;

use kissj\Event\Event;
use kissj\Participant\Participant;
use LeanMapper\Entity;

/**
 * Class User
 * @property int         $id
 * @property string      $email
 * @property Event       $event   m:hasOne
 * @property string      $status  m:enum(self::STATUS_*)
 * @property Participant $participant
 */
class User extends Entity {
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
}
