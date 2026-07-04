<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeInterface;
use kissj\Orm\EntityDatetime;
use kissj\Participant\Participant;

/**
 * @property int                    $id
 * @property Programme              $programme m:hasOne
 * @property Participant            $participant m:hasOne
 * @property DateTimeInterface|null $deletedAt m:passThru(dateFromString|dateToString)
 */
class ProgrammeRegistration extends EntityDatetime
{
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
