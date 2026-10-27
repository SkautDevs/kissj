<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\Drivers\PostgreDriver;
use Dibi\Fluent;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Repository;
use kissj\Participant\ParticipantRole;
use RuntimeException;

class ProgrammeRepository extends Repository
{
    /**
     * @return list<Programme>
     */
    public function findActiveForEvent(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->where('event_id = %i', $event->id);
        $qb->where('deleted_at IS NULL');
        $qb->orderBy('start')->asc();

        return $this->hydrateProgrammes($qb->fetchAll());
    }

    /**
     * @return list<Programme>
     */
    public function findActiveForRole(Event $event, ParticipantRole $role): array
    {
        return $this->hydrateProgrammes($this->createActiveForRoleFluent($event, $role)->fetchAll());
    }

    /**
     * @return list<Programme>
     */
    public function findActivePreregisteredForRole(Event $event, ParticipantRole $role): array
    {
        $qb = $this->createActiveForRoleFluent($event, $role);
        $qb->where('programme.is_preregistered = %b', true);

        return $this->hydrateProgrammes($qb->fetchAll());
    }

    private function createActiveForRoleFluent(Event $event, ParticipantRole $role): Fluent
    {
        $qb = $this->createFluent();
        $qb->where('roles LIKE %~like~', Programme::encodeRoleValues([$role->value]));
        $qb->where('event_id = %i', $event->id);
        $qb->where('deleted_at IS NULL');
        $qb->orderBy('start')->asc();

        return $qb;
    }

    /**
     * @return array<int, int>
     */
    public function getActiveOccupancy(Event $event): array
    {
        $qb = $this->connection->select('pr.programme_id, COUNT(*) AS taken')
            ->from('programme_registration')->as('pr')
            ->join('programme')->as('p')->on('p.id = pr.programme_id')
            ->where('p.event_id = %i', $event->id)
            ->where('pr.deleted_at IS NULL')
            ->where('p.deleted_at IS NULL')
            ->groupBy('pr.programme_id');

        /** @var array<int, int> $occupancy */
        $occupancy = $qb->fetchPairs('programme_id', 'taken');

        return $occupancy;
    }

    public function findActiveById(int $programmeId, Event $event): ?Programme
    {
        $programme = $this->findOneBy(['id' => $programmeId]);
        if (
            $programme instanceof Programme === false
            || $programme->isDeleted()
            || $programme->event->id !== $event->id
        ) {
            return null;
        }

        return $programme;
    }

    public function getActiveById(int $programmeId, Event $event): Programme
    {
        $programme = $this->findActiveById($programmeId, $event);
        if ($programme === null) {
            throw new RuntimeException('Programme not found for event');
        }

        return $programme;
    }

    public function getForUpdate(int $programmeId): ?Programme
    {
        $lockClause = $this->connection->getDriver() instanceof PostgreDriver ? ' FOR UPDATE' : '';
        $row = $this->connection->fetch(
            'SELECT * FROM %n WHERE id = %i' . $lockClause,
            $this->getTable(),
            $programmeId,
        );
        if ($row === null) {
            return null;
        }

        /** @var Row&iterable<string, mixed> $row */
        /** @var Programme $programme */
        $programme = $this->createEntity($row);

        return $programme;
    }

    /**
     * @return list<string>
     */
    public function findExclusiveGroupNames(Event $event): array
    {
        $qb = $this->connection->select('DISTINCT exclusive_group_name')
            ->from('programme')
            ->where('event_id = %i', $event->id)
            ->where('deleted_at IS NULL')
            ->where('exclusive_group_name IS NOT NULL')
            ->orderBy('exclusive_group_name');

        /** @var list<string> $names */
        $names = $qb->fetchPairs(null, 'exclusive_group_name');

        return $names;
    }

    /**
     * @param array<int, Row> $rows
     * @return list<Programme>
     */
    private function hydrateProgrammes(array $rows): array
    {
        $programmes = [];
        foreach ($rows as $row) {
            /** @var Row&iterable<string, mixed> $row */
            /** @var Programme $programme */
            $programme = $this->createEntity($row);
            $programmes[] = $programme;
        }

        return $programmes;
    }
}
