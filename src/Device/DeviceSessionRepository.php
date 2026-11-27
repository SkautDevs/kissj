<?php

declare(strict_types=1);

namespace kissj\Device;

use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
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

    public function deleteExpiredBefore(Event $event, DateTimeInterface $before): void
    {
        $this->connection->delete($this->getTable())
            ->where('expires_at < %s', DateTimeUtils::formatAtom($before))
            ->where(
                'participant_id IN (SELECT pa.id FROM participant pa'
                . ' LEFT JOIN participant pl ON pl.id = pa.patrol_leader_id'
                . ' JOIN [user] u ON u.id = COALESCE(pa.user_id, pl.user_id)'
                . ' WHERE u.event_id = %i)',
                $event->id,
            )
            ->execute();
    }
}
