<?php

declare(strict_types=1);

namespace kissj\Participant;

use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Entry\EntryParticipant;
use kissj\Entry\EntryStatus;
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
 * @method Participant getOneBy(mixed[] $criteria)
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
        $qb->where('u.role = %s', UserRole::Participant);
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

        if (in_array(ParticipantRole::PatrolParticipant, $roles, true)) {
            $qb = $this->createFluent();

            $qb->join('participant')->as('pl')->on('pl.id = participant.patrol_leader_id');
            $qb->join('user')->as('u')->on('u.id = pl.user_id');

            $qb->where('participant.role = %s', ParticipantRole::PatrolParticipant);
            $qb->where('u.status IN %in', $statuses);
            $qb->where('u.event_id = %i', $event->id);

            $this->addOrdersBy($qb, $orders);

            if ($adminUser instanceof User) {
                $this->addFilterAdminParticipants($qb, $adminUser);
            }
            $rows = $qb->fetchAll();
            /** @var Participant[] $participants */
            $participants = [...$participants, ...$this->createEntities($rows)];
        }

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
        $qb->where('u.role = %s', UserRole::Participant);
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
                $qb->where('participant.role = %s', ParticipantRole::Ist);
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
        return array_filter(
            $participants,
            fn (Participant $participant): bool => $participant->isFullNameNotEmpty(),
        );
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

    /**
     * @return array<string,int>
     */
    public function getIstArrivalStatistic(
        Event $event,
    ): array {
        $qb = $this->connection->select('date(participant.arrival_date) as ad, COUNT(*)')->from($this->getTable());
        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('participant.role = %s', ParticipantRole::Ist);
        $qb->where('u.role = %s', UserRole::Participant);
        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        $qb->groupBy('date(participant.arrival_date)');
        $qb->orderBy('date(participant.arrival_date)');

        /** @var array<string,int> $rows */
        $rows = $qb->fetchPairs('ad', 'count');

        return $rows;
    }

    /**
     * @return array<string,int>
     */
    public function getFoodStatistic(
        Event $event,
    ): array {
        $qb = $this->connection->select('participant.food_preferences as f, COUNT(*)')->from($this->getTable());
        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('u.role = %s', UserRole::Participant);
        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        $qb->groupBy('participant.food_preferences');
        $qb->orderBy('participant.food_preferences');

        /** @var array<string,int> $rows */
        $rows = $qb->fetchPairs('f', 'count');

        return $rows;
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

        $qb->where('u.role = %s', UserRole::Participant);
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

    public function findOneByTieCodeAndEvent(string $tieCode, Event $authorizedEvent): ?Participant
    {
        $participant = $this->findOneBy(['tie_code' => $tieCode]);
        if ($participant === null) {return null;}
        if ($participant->user->event == $authorizedEvent) {
            return $participant;
        }
        return null;
    }
    public function findParticipantById(int $participantId, Event $event): ?Participant
    {
        $participant = $this->findOneBy(['id' => $participantId]);
        if ($participant === null) {
            return null;
        }

        if ($participant->getUserButNotNull()->event->id !== $event->id) {
            return null;
        }

        return $participant;
    }

    public function getParticipantById(int $participantId, Event $event): Participant
    {
        $participant = $this->findParticipantById($participantId, $event);
        if ($participant === null) {
            throw new RuntimeException(sprintf('Participant with ID %s not found', $participantId));
        }

        return $participant;
    }

    /**
     * @return array<string, array<EntryParticipant>>
     */
    public function getParticipantsForEntry(Event $event): array
    {
        $rows = $this->getRowsForEntryParticipant($event, [
            ParticipantRole::PatrolLeader,
            ParticipantRole::TroopLeader,
            ParticipantRole::Ist,
            ParticipantRole::Guest,
        ]);

        $participants = [];
        foreach($rows as $row) {
            /** @var string $role */
            $role = $row['role'];
            $participants[$role][$row['id']] = $this->mapDataToEntryParticipant($row);
        }

        $rowsDependableParticipants = $this->getRowsForEntryParticipant($event, [
            ParticipantRole::PatrolParticipant,
            ParticipantRole::TroopParticipant,
        ]);

        foreach ($rowsDependableParticipants as $rowDependableParticipant) {
            $role = match ($rowDependableParticipant['role']) {
                ParticipantRole::TroopParticipant->value => ParticipantRole::TroopLeader->value,
                ParticipantRole::PatrolParticipant->value => ParticipantRole::PatrolLeader->value,
                default => 'unknown',
            };

            $leader = $participants[$role][$rowDependableParticipant['patrol_leader_id']] ?? null;
            if ($leader instanceof EntryParticipant) {
                $leader->participants[$rowDependableParticipant['id']]
                    = $this->mapDataToEntryParticipant($rowDependableParticipant);
            }
        }

        return $participants;
    }

    /**
     * @param Event $event
     * @param array<ParticipantRole> $participantRoles
     * @return array<Row>
     * /
     */
    private function getRowsForEntryParticipant(Event $event, array $participantRoles): array
    {
        $qb = $this->connection->select('
            participant.id,
            participant.first_name,
            participant.last_name,
            participant.nickname,
            participant.patrol_name,
            participant.tie_code,
            participant.birth_date,
            participant.role,
            participant.patrol_leader_id,
            participant.entry_date,
            d.is_done AS sfh_done
        ')->from($this->getTable());

        $qb->join('user')->as('u')->on('u.id = participant.user_id');
        $qb->leftJoin('deal')->as('d')->on('participant.id = d.participant_id')->and('d.slug = %s', 'sfh');

        $qb->where('u.role = %s', UserRole::Participant);
        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        $qb->where('participant.role IN %in', $participantRoles);

        $this->addOrdersBy($qb, [new Order('id')]);

        return $qb->fetchAll();
    }

    private function mapDataToEntryParticipant(Row $row): EntryParticipant
    {
        /** @var array{
         *     id: int,
         *     first_name: string|null,
         *     last_name: string|null,
         *     nickname: string|null,
         *     patrol_name: string|null,
         *     tie_code: string,
         *     birth_date: \DateTimeInterface|null,
         *     patrol_leader_id: int|null,
         *     sfh_done: bool|null,
         *     entry_date: \DateTimeInterface|null,
         *     role: string|null
         * } $array */
        $array = $row->toArray();

        return new EntryParticipant(
            $array['id'],
            $array['first_name'] ?? '',
            $array['last_name'] ?? '',
            $array['nickname'] ?? '',
            $array['patrol_name'] ?? '',
            $array['tie_code'],
            $array['birth_date'] ?? DateTimeUtils::getDateTime(),
            EntryStatus::fromDatetime($array['entry_date']),
            $array['sfh_done'] ?? false,
        );
    }
}
