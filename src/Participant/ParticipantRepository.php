<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\User\User;

/**
 * @method Participant[] findAll()
 * @method Participant get(int $participantId)
 * @method Participant[] findBy(mixed[] $criteria)
 * @method Participant|null findOneBy(mixed[] $criteria)
 * @method Participant getOneBy(mixed[] $criteria)
 */
class ParticipantRepository extends Repository
{
    /**
     * TODO optimalize
     *
     * @param string[] $roles
     * @param string[] $statuses
     * @return Participant[]
     */
    public function getAllParticipantsWithStatus(
        array $roles,
        array $statuses,
        Event $event,
        ?User $adminUser = null,
        ?Order $order = null,
        bool $filterEmptyParticipants = false,
    ): array {
        $participants = $this->findAll();

        if ($adminUser instanceof User) {
            $participants = $this->filterContingentAdminParticipants($participants, $adminUser);
        }

        $validParticipants = [];
        foreach ($participants as $participant) {
            $user = $participant->getUserButNotNull();
            if (
                $user->event->id === $event->id
                && in_array($user->status, $statuses, true)
                && in_array($participant->role, $roles, true)
            ) {
                $validParticipants[$participant->id] = $participant;
            }
        }

        if ($order instanceof Order) {
            uasort(
                $validParticipants,
                function (Participant $firstParticipant, Participant $secondParticipant) use ($order): int {
                    $fieldName = $order->getField();

                    $result = $firstParticipant->$fieldName <=> $secondParticipant->$fieldName;

                    return $order->isOrderAsc() ? $result : -$result;
                }
            );
        }

        if ($filterEmptyParticipants) {
            $validParticipants = $this->filterEmptyParticipants($validParticipants);
        }

        return $validParticipants;
    }

    /**
     * @param Participant[] $participants
     * @param User $adminUser
     * @return Participant[]
     */
    private function filterContingentAdminParticipants(array $participants, User $adminUser): array
    {
        return match ($adminUser->role) {
            User::ROLE_ADMIN => $participants,
            User::ROLE_CONTINGENT_ADMIN_CS => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_CZECHIA;
            }),
            User::ROLE_CONTINGENT_ADMIN_SK => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_SLOVAKIA;
            }),
            User::ROLE_CONTINGENT_ADMIN_PL => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_POLAND;
            }),
            User::ROLE_CONTINGENT_ADMIN_HU => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_HUNGARY;
            }),
            User::ROLE_CONTINGENT_ADMIN_EU => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_EUROPEAN;
            }),
            User::ROLE_CONTINGENT_ADMIN_RO => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_ROMANIA;
            }),
            default => [],
        };
    }

    /**
     * @param Participant[] $participants
     * @return Participant[]
     */
    private function filterEmptyParticipants(array $participants): array
    {
        return array_filter($participants, function (Participant $participant): bool {
            return $participant->getFullName() !== ' ';
        });
    }
}
