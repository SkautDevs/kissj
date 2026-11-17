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
 * @table device_session
 *
 * @method DeviceSession get(int $sessionId)
 * @method DeviceSession getOneBy(mixed[] $criteria)
 * @method DeviceSession[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method DeviceSession|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class DeviceSessionRepository extends Repository
{
    public function findByTokenHash(string $tokenHash): ?DeviceSession
    {
        return $this->findOneBy(['token_hash' => $tokenHash]);
    }

    public function countActiveForParticipant(Participant $participant, DateTimeInterface $now): int
    {
        return $this->countBy([
            'participant' => $participant,
            'expires_at' => new Relation(DateTimeUtils::formatAtom($now), '>'),
        ]);
    }
}
