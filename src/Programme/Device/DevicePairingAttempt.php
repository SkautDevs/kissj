<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int         $id
 * @property Participant $participant m:hasOne
 */
class DevicePairingAttempt extends EntityDatetime
{
}
