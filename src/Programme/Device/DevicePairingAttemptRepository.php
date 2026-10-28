<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Orm\Order;
use kissj\Orm\Relation;
use kissj\Orm\Repository;
use kissj\Participant\Participant;

/**
 * @table device_pairing_attempt
 *
 * @method DevicePairingAttempt get(int $attemptId)
 * @method DevicePairingAttempt getOneBy(mixed[] $criteria)
 * @method DevicePairingAttempt[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method DevicePairingAttempt|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class DevicePairingAttemptRepository extends Repository
{
    public function countSince(Participant $participant, DateTimeInterface $since): int
    {
        return $this->countBy([
            'participant' => $participant,
            'created_at' => new Relation(DateTimeUtils::formatAtom($since), '>='),
        ]);
    }

    public function deleteOlderThan(Participant $participant, DateTimeInterface $before): void
    {
        $this->connection->delete($this->getTable())
            ->where('participant_id = %i', $participant->id)
            ->where('created_at < %s', DateTimeUtils::formatAtom($before))
            ->execute();
    }
}
