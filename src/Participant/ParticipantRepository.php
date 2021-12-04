<?php

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Orm\Repository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\User;

class ParticipantRepository extends Repository
{
    /**
     * @return Participant[]
     */
    public function getAllNonOpenParticipantsFromEvent(Event $event): array
    {
        /** @var Participant[] $participants */
        $participants = $this->findAll();

        return array_filter($participants, function (Participant $participant) use ($event): bool {
            $user = $participant->user;
            if ($user === null 
                && $participant instanceof PatrolParticipant 
            ) {
                $user = $participant->patrolLeader->user;
            } elseif ($user === null) {
                return false;
            }

            return $user?->status !== User::STATUS_OPEN
                && $user->event->id !== $event->id;
        });
    }

    /**
     * @return Participant[]
     */
    public function getAllPaidParticipantsFromEvent(Event $event): array
    {
        return array_filter(
            $this->getAllNonOpenParticipantsFromEvent($event),
            function (Participant $participant): bool {
                $user = $participant->user;
                if ($user === null
                    && $participant instanceof PatrolParticipant
                ) {
                    $user = $participant->patrolLeader->user;
                } elseif ($user === null) {
                    return false;
                }

                return $user?->status === User::STATUS_PAID;
            }
        );
    }
}
