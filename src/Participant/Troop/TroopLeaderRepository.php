<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;

/**
 * @table participant
 *
 * @method TroopLeader get(int $troopLeaderId)
 * @method TroopLeader getOneBy(mixed[] $criteria)
 * @method TroopLeader[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method TroopLeader|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class TroopLeaderRepository extends Repository
{
    public function findTroopLeaderFromTieCode(string $tieCode, Event $event): ?TroopLeader
    {
        $troopLeader = $this->findOneBy([
            'tie_code' => strtoupper($tieCode),
            'role' => ParticipantRole::TroopLeader,
        ]);
        if ($troopLeader?->getUserButNotNull()->event->id !== $event->id) {
            return null;
        }

        return $troopLeader;
    }

    public function getFromUser(User $user): TroopLeader
    {
        return $this->getOneBy(['user' => $user]);
    }
}
