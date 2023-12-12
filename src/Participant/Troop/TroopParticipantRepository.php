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
 * @method TroopParticipant get(int $istId)
 * @method TroopParticipant getOneBy(mixed[] $criteria)
 * @method TroopParticipant[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method TroopParticipant|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class TroopParticipantRepository extends Repository
{
    public function findTroopParticipantFromTieCode(string $tieCode, Event $event): ?TroopParticipant
    {
        $troopParticipant = $this->findOneBy([
            'tie_code' => strtoupper($tieCode),
            'role' => ParticipantRole::TroopParticipant,
        ]);
        if ($troopParticipant?->getUserButNotNull()->event->id !== $event->id) {
            return null;
        }

        return $troopParticipant;
    }

    /**
     * @return TroopParticipant[]
     */
    public function findAllTroopParticipantsForTroopLeader(TroopLeader $troopLeader): array
    {
        return $this->findBy(['patrol_leader_id' => $troopLeader->id]);
    }

    public function getFromUser(User $user): TroopParticipant
    {
        return $this->getOneBy(['user' => $user]);
    }

    /**
     * @return TroopParticipant[]
     */
    public function getAllWithoutTroop(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->where('participant.role = %s', ParticipantRole::TroopParticipant);
        $qb->where('u.event_id = %i', $event->id);
        $qb->where('participant.patrol_leader_id IS NULL');

        /** @var TroopParticipant[] $troopParticipants */
        $troopParticipants = $this->createEntities($qb->fetchAll());

        return $troopParticipants;
    }
}
