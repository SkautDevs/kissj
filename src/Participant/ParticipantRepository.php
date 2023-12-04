<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\User\User;
use kissj\User\UserRole;
use kissj\User\UserStatus;

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
     * TODO optimize
     *
     * @param ParticipantRole[] $roles
     * @param UserStatus[] $statuses
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
            UserRole::Admin => $participants,
            UserRole::ContingentAdminCs => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_CZECHIA;
            }),
            UserRole::ContingentAdminSk => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_SLOVAKIA;
            }),
            UserRole::ContingentAdminPl => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_POLAND;
            }),
            UserRole::ContingentAdminHu => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_HUNGARY;
            }),
            UserRole::ContingentAdminEu => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_EUROPEAN;
            }),
            UserRole::ContingentAdminRo => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_ROMANIA;
            }),
            UserRole::ContingentAdminGb => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_BRITAIN;
            }),
            UserRole::ContingentAdminSw => array_filter($participants, function (Participant $p): bool {
                return $p->contingent === EventTypeCej::CONTINGENT_SWEDEN;
            }),
            default => [],
        };
    }

    /**
     * @return Participant[]
     */
    public function getPaidParticipantsWithExactPayments(Event $event, int $countPayments, int $limit): array
    {
        $participants = $this->getAllParticipantsWithStatus(
            [
                ParticipantRole::Ist,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
            ],
            [
                UserStatus::Paid,
            ],
            $event,
            null,
            new Order('id'),
        );

        $participants = array_slice($participants, 0, $limit);

        $participants = array_filter($participants, function (Participant $participant) use ($countPayments): bool {
            return count($participant->getNoncanceledPayments()) === $countPayments;
        });

        return $participants;
    }

    /**
     * @param Participant[] $participants
     * @return Participant[]
     */
    private function filterEmptyParticipants(array $participants): array
    {
        return array_filter($participants, function (Participant $participant): bool {
            return $participant->isFullNameNotEmpty();
        });
    }

    public function getParticipantFromUser(User $user): Participant
    {
        return $this->getOneBy(['user' => $user]);
    }

    public function findParticipantFromUser(?User $user): ?Participant
    {
        if ($user === null) {
            return null;
        }

        return $this->findOneBy(['user' => $user]);
    }

    public function findOneByEntryCode(string $entryCode): ?Participant
    {
        return $this->findOneBy(['entry_code' => $entryCode]);
    }
}
