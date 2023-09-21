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
 * @method TroopParticipant get(int $istId)
 * @method TroopParticipant[] findBy(mixed[] $criteria)
 * @method TroopParticipant|null findOneBy(mixed[] $criteria)
 * @method TroopParticipant getOneBy(mixed[] $criteria)
 */
class TroopParticipantRepository extends Repository
{
    /**
     * @param Event $event
     * @return TroopParticipant[]
     */
    public function findAllWithEvent(Event $event): array
    {
        $troopParticipants = [];
        foreach ($this->findAll() as $participant) {
            if ($participant instanceof TroopParticipant && $participant->getUserButNotNull()->event->id === $event->id) {
                $troopParticipants[] = $participant;
            }
        }

        return $troopParticipants;
    }

    public function findTroopParticipantFromTieCode(string $tieCode, Event $event): ?TroopParticipant
    {
        $troopParticipant = $this->findOneBy([
            'tie_code' => strtoupper($tieCode),
            'role' => ParticipantRole::TroopParticipant,
        ]);
        if ($troopParticipant?->user->event->id !== $event->id) {
            return null;
        }

        return $troopParticipant;
    }

    public function findAllTroopParticipantsForTroopLeader(TroopLeader $troopLeader): array
    {
        return $this->findBy(['patrol_leader_id' => $troopLeader->id]);
    }

    // TODO check why?
    public function getFromUser(User $user): TroopParticipant
    {
        return $this->getOneBy(['user' => $user]);
    }
}
