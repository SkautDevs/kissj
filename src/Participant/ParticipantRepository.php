<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Admin\StatisticUserValueObject;
use kissj\User\User;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use LeanMapper\Fluent;
use RuntimeException;

/**
 * @method Participant get(int $participantId)
 * @method Participant getOneBy(mixed[] $criteria)
 * @method Participant[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method Participant|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class ParticipantRepository extends Repository
{
    /**
     * @param ParticipantRole[] $roles
     * @param UserStatus[] $statuses
     * @param Order[] $orders
     * @return Participant[]
     */
    public function getAllParticipantsWithStatus(
        array $roles,
        array $statuses,
        Event $event,
        ?User $adminUser = null,
        array $orders = [],
        bool $filterEmptyParticipants = false,
        ?int $limit = null,
    ): array {
        $qb = $this->createFluent();

        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('participant.role IN %in', $roles);
        $qb->where('u.status IN %in', $statuses);
        $qb->where('u.event_id = %i', $event->id);

        $this->addOrdersBy($qb, $orders);

        if ($adminUser instanceof User) {
            $this->addFilterAdminParticipants($qb, $adminUser);
        }

        if ($limit !== null) {
            $qb->limit($limit);
        }

        $rows = $qb->fetchAll();
        /** @var Participant[] $participants */
        $participants = $this->createEntities($rows);

        if ($filterEmptyParticipants) {
            $participants = $this->filterEmptyParticipants($participants);
        }

        return $participants;
    }

    /**
     * @param ParticipantRole[] $roles
     * @param UserStatus[] $statuses
     */
    public function getParticipantsCount(
        array $roles,
        array $statuses,
        Event $event,
    ): int {
        $qb = $this->connection->select('COUNT(*)')->from($this->getTable());

        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('participant.role IN %in', $roles);
        $qb->where('u.status IN %in', $statuses);
        $qb->where('u.event_id = %i', $event->id);

        /** @var int $row */
        $row = $qb->fetchSingle();

        return $row;
    }

    private function addFilterAdminParticipants(Fluent $qb, User $adminUser): void
    {
        switch ($adminUser->role) {
            case UserRole::Admin:
                // allow all
                break;
            case UserRole::IstAdmin:
                // TODO
                break;
            case UserRole::ContingentAdminCs:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_CZECHIA);
                break;
            case UserRole::ContingentAdminSk:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_SLOVAKIA);
                break;
            case UserRole::ContingentAdminPl:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_POLAND);
                break;
            case UserRole::ContingentAdminHu:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_HUNGARY);
                break;
            case UserRole::ContingentAdminEu:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_EUROPEAN);
                break;
            case UserRole::ContingentAdminRo:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_ROMANIA);
                break;
            case UserRole::ContingentAdminGb:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_BRITAIN);
                break;
            case UserRole::ContingentAdminSw:
                $qb->where('participant.contingent = %s', EventTypeCej::CONTINGENT_SWEDEN);
                break;
            case UserRole::Participant:
                throw new RuntimeException('UserRole Participant is used as administrator');
        }
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
            orders: [new Order('id')],
            limit: $limit,
        );

        # TODO optimize - non urgent, it is used only for events with multiple payments
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

    /**
     * @param string[] $contingents
     * @return StatisticUserValueObject[]
     */
    public function getContingentStatistic(
        Event $event,
        ParticipantRole $role,
        array $contingents,
    ): array {
        $statistics = [];
        foreach ($contingents as $contingent) {
            $statistics[$contingent] = $this->getStatistic(
                $event,
                $role,
                $contingent,
            );
        }

        return $statistics;
    }

    public function getStatistic(
        Event $event,
        ParticipantRole $role,
        ?string $contingent = null,
    ): StatisticUserValueObject {
        $qb = $this->connection->select('u.status, COUNT(*)')->from($this->getTable());
        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('u.event_id = %i', $event->id);
        $qb->where('participant.role = %s', $role);
        if ($contingent !== null) {
            $qb->where('participant.contingent = %s', $contingent);
        }

        $qb->groupBy('u.status');

        $rows = $qb->fetchPairs('status', 'count');

        return new StatisticUserValueObject(
            $rows[UserStatus::Open->value] ?? 0,
            $rows[UserStatus::Closed->value] ?? 0,
            $rows[UserStatus::Approved->value] ?? 0,
            $rows[UserStatus::Paid->value] ?? 0,
        );
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
