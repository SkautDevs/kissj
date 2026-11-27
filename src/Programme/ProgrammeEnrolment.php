<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Orm\SoftDeletable;
use kissj\Participant\Participant;

/**
 * @property int                    $id
 * @property Programme              $programme m:hasOne
 * @property Participant            $participant m:hasOne
 * @property DateTimeInterface|null $deletedAt m:passThru(dateFromString|dateToString)
 */
class ProgrammeEnrolment extends EntityDatetime implements SoftDeletable
{
}
