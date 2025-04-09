<?php

declare(strict_types=1);

namespace kissj\Participant;

use DateTime;
use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Entry\EntryParticipant;
use kissj\Entry\EntryStatus;
use kissj\Event\Event;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Admin\StatisticUserValueObject;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolsRoster;
use kissj\Participant\Patrol\SinglePatrolRoster;
use kissj\Participant\Troop\TroopParticipant;
use kissj\User\User;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use LeanMapper\Fluent;
use RuntimeException;

/**
 * @method Participant getOneBy(mixed[] $criteria)
 * @method Participant|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 * @method Participant[] findBy(mixed[] $criteria, Order[] $orders = [])
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
        bool $sortedByTroopOrPatrol = false,
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

        if ($sortedByTroopOrPatrol) {
            $participants = $this->sortParticipantsBasedOnTroopOrPatrol($participants);
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
     * @param Participant[] $participants
     * @return Participant[]
     */
    private function sortParticipantsBasedOnTroopOrPatrol(array $participants): array
    {
        usort(
            $participants,
            function (Participant $a, Participant $b) {
                if ($a instanceof PatrolParticipant && $b instanceof PatrolParticipant) {
                    return $a->patrolLeader->id <=> $b->patrolLeader->id;
                }

                if ($a instanceof TroopParticipant && $b instanceof TroopParticipant) {
                    return ($a->troopLeader->id ?? $a->id) <=> ($b->troopLeader->id ?? $a->id);
                }

                return $a->id <=> $b->id;
            },
        );

        return $participants;
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
    public function getEntryStatisticGlobal(
        Event $event
    ): array {
        return [
            'coming' => $this->countEntryComing($event),
            'arrived' => $this->countEntryArrived($event),
            'leave' => $this->countEntryLeave($event),
        ];
    }

    /**
     * @return array<string,array<string,int>>
     */
    public function getEntryStatisticForAllowedRoles(Event $event): array
    {
        $entries = [];
        if ($event->allowPatrols) {
            $entries[ParticipantRole::PatrolLeader->value]
                = $this->getEntryStatisticForRole($event, ParticipantRole::PatrolLeader);
        }

        if ($event->allowTroops) {
            $entries[ParticipantRole::TroopLeader->value]
                = $this->getEntryStatisticForRole($event, ParticipantRole::TroopLeader);
        }

        if ($event->allowIsts) {
            $entries[ParticipantRole::Ist->value]
                = $this->getEntryStatisticForRole($event, ParticipantRole::Ist);
        }

        return $entries;
    }

    /**
     * @return array<string,int>
     */
    public function getEntryStatisticForRole(
        Event $event,
        ParticipantRole $participantRole,
    ): array {
        return [
            'coming' => $this->countEntryComing($event, $participantRole),
            'arrived' => $this->countEntryArrived($event, $participantRole),
            'leave' => $this->countEntryLeave($event, $participantRole),
        ];
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
        if ($participant === null) {
            return null;
        }

        if ($participant->user?->event->id === $authorizedEvent->id) {
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
        foreach ($rows as $row) {
            /** @var string $role */
            $role = $row['role'];
            /** @var string $id */
            $id = $row['id'];
            $participants[$role][$id] = $this->mapDataToEntryParticipant($row);
        }

        $rowsDependableParticipants = array_merge(
            $this->getRowsForEntryParticipant($event, [ParticipantRole::TroopParticipant]),
            $this->getRowsForEntryPatrolParticipant($event),
        );

        foreach ($rowsDependableParticipants as $rowDependableParticipant) {
            /** @var string $dpId */
            $dpId = $rowDependableParticipant['id'];
            /** @var string $dpRole */
            $dpRole = $rowDependableParticipant['role'];
            /** @var string $dpPatrolLeaderId */
            $dpPatrolLeaderId = $rowDependableParticipant['patrol_leader_id'];

            $role = match ($dpRole) {
                ParticipantRole::TroopParticipant->value => ParticipantRole::TroopLeader->value,
                ParticipantRole::PatrolParticipant->value => ParticipantRole::PatrolLeader->value,
                default => 'unknown',
            };

            $leader = $participants[$role][$dpPatrolLeaderId] ?? null;
            if ($leader instanceof EntryParticipant) {
                $leader->participants[$dpId]
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
            participant.leave_date,
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

    /**
     * @param Event $event
     * @return array<Row>
     * /
     */
    private function getRowsForEntryPatrolParticipant(Event $event): array
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
            participant.leave_date,
            d.is_done AS sfh_done
        ')->from($this->getTable());

        $qb->join('participant')->as('pl')->on('pl.id = participant.patrol_leader_id');
        $qb->join('user')->as('u')->on('u.id = pl.user_id');
        $qb->leftJoin('deal')->as('d')->on('participant.id = d.participant_id')->and('d.slug = %s', 'sfh');

        $qb->where('u.role = %s', UserRole::Participant);
        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        $qb->where('participant.role = %s', ParticipantRole::PatrolParticipant);

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
         *     leave_date: \DateTimeInterface|null,
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
            EntryStatus::entryFromDatetime(
                $array['entry_date'],
                $array['leave_date'],
            ),
            $array['sfh_done'] ?? false,
        );
    }

    public function getPatrolsRoster(Event $event): PatrolsRoster
    {
        $singlePatrolsRoster = [];

        $patrolLeaders = $this->getAllPaidPatrolLeaders($event);

        foreach ($patrolLeaders as $pl) {
            $singlePatrolsRoster[] = new SinglePatrolRoster(
                (string)$pl->id,
                $pl->patrolName ?? '',
                $pl->getFullName(),
                array_map(
                    fn (PatrolParticipant $pp): string => $pp->getFullName(),
                    $pl->patrolParticipants,
                ),
            );
        }

        return new PatrolsRoster($singlePatrolsRoster);
    }

    /**
     * @return array<PatrolLeader>
     */
    private function getAllPaidPatrolLeaders(Event $event): array
    {
        /** @var array<PatrolLeader> $patrolLeaders */
        $patrolLeaders = $this->getAllParticipantsWithStatus(
            [ParticipantRole::PatrolLeader],
            [UserStatus::Paid],
            $event,
        );

        return $patrolLeaders;
    }

    private function countEntryComing(
        Event $event,
        ?ParticipantRole $participantRole = null,
    ): int {
        $qb = $this->getQueryBuilderCountForPaidParticipants($event, $participantRole);
        $qb->where('participant.entry_date IS NULL');
        $qb->where('participant.leave_date IS NULL');

        /** @var string $countParticipants */
        $countParticipants = $qb->fetchSingle();

        // count patrol participants and merge
        $qb = $this->getQueryBuilderCountForPaidPatrolParticipants($event, $participantRole);
        $qb->where('participant.entry_date IS NULL');
        $qb->where('participant.leave_date IS NULL');

        /** @var string $countPatrolParticipants */
        $countPatrolParticipants = $qb->fetchSingle();

        return (int)$countParticipants + (int)$countPatrolParticipants;
    }

    private function countEntryArrived(
        Event $event,
        ?ParticipantRole $participantRole = null,
    ): int {
        $qb = $this->getQueryBuilderCountForPaidParticipants($event, $participantRole);
        $qb = $qb->where('participant.entry_date IS NOT NULL');
        $qb = $qb->where('participant.leave_date IS NULL');

        /** @var string $countParticipants */
        $countParticipants = $qb->fetchSingle();

        // count patrol participants and merge
        $qb = $this->getQueryBuilderCountForPaidPatrolParticipants($event, $participantRole);
        $qb->where('participant.entry_date IS NOT NULL');
        $qb->where('participant.leave_date IS NULL');

        /** @var string $countPatrolParticipants */
        $countPatrolParticipants = $qb->fetchSingle();

        return (int)$countParticipants + (int)$countPatrolParticipants;
    }

    private function countEntryLeave(
        Event $event,
        ?ParticipantRole $participantRole = null,
    ): int {
        $qb = $this->getQueryBuilderCountForPaidParticipants($event, $participantRole);
        $qb->where('participant.leave_date IS NOT NULL');

        /** @var string $countParticipants */
        $countParticipants = $qb->fetchSingle();

        // count patrol participants and merge
        $qb = $this->getQueryBuilderCountForPaidPatrolParticipants($event, $participantRole);
        $qb->where('participant.leave_date IS NOT NULL');

        /** @var string $countPatrolParticipants */
        $countPatrolParticipants = $qb->fetchSingle();

        return (int)$countParticipants + (int)$countPatrolParticipants;
    }

    private function getQueryBuilderCountForPaidParticipants(
        Event $event,
        ?ParticipantRole $participantRole = null
    ): Fluent {
        $qb = $this->connection->select('COUNT(participant.id)')->from($this->getTable());
        $qb->join('user')->as('u')->on('u.id = participant.user_id');

        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        if ($participantRole !== null) {
            $qb->where('participant.role = %s', $participantRole);
        }

        return $qb;
    }

    private function getQueryBuilderCountForPaidPatrolParticipants(
        Event $event,
        ?ParticipantRole $participantRole = null,
    ): Fluent {
        $qb = $this->connection->select('COUNT(participant.id)')->from($this->getTable());
        $qb->join('participant')->as('pl')->on('pl.id = participant.patrol_leader_id');
        $qb->join('user')->as('u')->on('u.id = pl.user_id');

        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);

        if ($participantRole !== null) {
            $qb->where('participant.role = %s', $participantRole);
        }

        return $qb;
    }

    # retrieve count of food diets ordered by role (or pl if patrols/troops are allowed) and event days. Taking into account arrival dates for all participants.
    # returns array<role, array<day, array<diet, count of food>>

    /**
     * @return array<string, array<string, array<string, int>>>
     */
    public function getCompleteFoodStatistic(
        Event $event,
    ) :array
    {
        $eventParticipants = $this->getAllParticipantsWithStatus(
            [ParticipantRole::TroopLeader, ParticipantRole::PatrolLeader,  ParticipantRole::Ist,  ParticipantRole::Guest],
            [UserStatus::Paid],
            $event,
        );

        // TODO: [nice to have] implement new ORM criteria (or getAllParticipantsWithStatus atribute) "not null" as relation, so that array_filter can be removed
        $eventParticipants = array_filter(
            $eventParticipants,
            function (Participant $participant): bool {
                return $participant->foodPreferences !== null;
            });


        if ($eventParticipants === []) return [];

        return (new ParticipantFoodPlan($eventParticipants, $event))->toArray();

        //main logic


    }
    # retrieve absolute count of diets for event

    /**
     * @return array<string, int>
     */
    public function getDigestFoodStatistic(
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

        // count patrol participants and merge
        $qb = $this->connection->select('participant.food_preferences as f, COUNT(*)')->from($this->getTable());
        $qb->join('participant')->as('pl')->on('pl.id = participant.patrol_leader_id');
        $qb->join('user')->as('u')->on('u.id = pl.user_id');

        $qb->where('u.role = %s', UserRole::Participant);
        $qb->where('u.status = %s', UserStatus::Paid);
        $qb->where('u.event_id = %i', $event->id);


        $qb->groupBy('participant.food_preferences');
        $qb->orderBy('participant.food_preferences');

        /** @var array<string,int> $rows */
        $rowsPp = $qb->fetchPairs('f', 'count');

        // merge patrol participants into rest
        foreach ($rowsPp as $foodKey => $count) {
            if (array_key_exists($foodKey, $rows) === false) {
                $rows[$foodKey] = 0;
            }

            $rows[$foodKey] += $count;
        }

        return $rows;
    }
}
