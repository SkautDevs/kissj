<?php

declare(strict_types=1);

namespace kissj\Participant\Troop;

use kissj\Event\Event;
use kissj\Orm\Repository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;

/**
 * @table participant
 *
 * @method TroopLeader get(int $istId)
 * @method TroopLeader[] findBy(mixed[] $criteria)
 * @method TroopLeader|null findOneBy(mixed[] $criteria)
 * @method TroopLeader getOneBy(mixed[] $criteria)
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
