<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\Fluent;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\EntityNotFoundException;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;

/**
 * @method Programme get(int $programmeId)
 * @method Programme getOneBy(mixed[] $criteria)
 * @method Programme[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method Programme|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 * @method Programme|null findWithLock(int $programmeId)
 */
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
        $qb->orderBy('id')->asc();

        return $this->hydrateProgrammes($qb->fetchAll());
    }

    /**
     * @return list<Programme>
     */
    public function findActiveForParticipant(Participant $participant): array
    {
        $qb = $this->createFluent();
        $qb->join('programme_registration')->as('r')->on('r.programme_id = programme.id');
        $qb->where('r.participant_id = %i', $participant->id);
        $qb->where('r.deleted_at IS NULL');
        $qb->where('programme.deleted_at IS NULL');
        $qb->orderBy('programme.start')->asc();
        $qb->orderBy('programme.id')->asc();

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
            $programme === null
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
            throw new EntityNotFoundException('Programme not found for event');
        }

        return $programme;
    }

    /**
     * @param array<int, Row> $rows
     * @return list<Programme>
     */
    private function hydrateProgrammes(array $rows): array
    {
        /** @var array<int, Programme> $programmes */
        $programmes = $this->createEntities($rows);

        return array_values($programmes);
    }
}
