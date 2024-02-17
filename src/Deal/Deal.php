<?php

declare(strict_types=1);

namespace kissj\Deal;

use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int                    $id
 * @property string                 $slug
 * @property string                 $urlAddress
 * @property bool                   $isDone
 * @property DateTimeInterface|null $doneAt m:passThru(dateFromString|dateToString)
 * @property string                 $data
 *
 * @property Participant            $participant m:hasOne
 */
class Deal extends EntityDatetime
{
}
