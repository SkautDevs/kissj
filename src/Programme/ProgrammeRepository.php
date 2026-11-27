<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\EntityNotFoundException;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Programme\DTO\ProgrammePlaceCountsDTO;
use kissj\Programme\DTO\ProgrammeSectionCountsDTO;
use kissj\Programme\Place\ProgrammePlace;

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
        $qb->join('programme_enrolment')->as('r')->on('r.programme_id = programme.id');
        $qb->where('r.participant_id = %i', $participant->id);
        $qb->where('r.deleted_at IS NULL');
        $qb->orderBy('programme.start')->asc();
        $qb->orderBy('programme.id')->asc();

        return $this->hydrateProgrammes($qb->fetchAll());
    }

    /**
     * @return list<Programme>
     */
    public function findActiveAutoenrolledForRole(Event $event, ParticipantRole $role): array
    {
        $qb = $this->createFluent();
        $qb->where('roles LIKE %~like~', Programme::encodeRoleValues([$role->value]));
        $qb->where('event_id = %i', $event->id);
        $qb->where('programme.is_autoenrolled = %b', true);
        $qb->orderBy('start')->asc();

        return $this->hydrateProgrammes($qb->fetchAll());
    }

    public function findActiveById(int $programmeId, Event $event): ?Programme
    {
        $programme = $this->findOneBy(['id' => $programmeId]);
        if (
            $programme === null
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

    public function countIncludingDeletedForSection(ProgrammeSection $section): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(*)')
            ->from($this->getTable())
            ->where('section_id = %i', $section->id)
            ->fetchSingle();

        return $count;
    }

    public function countIncludingDeletedForPlace(ProgrammePlace $place): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(*)')
            ->from($this->getTable())
            ->where('place_id = %i', $place->id)
            ->fetchSingle();

        return $count;
    }

    /**
     * @return array<int, ProgrammeSectionCountsDTO>
     */
    public function countBySection(Event $event): array
    {
        /** @var list<Row> $rows */
        $rows = $this->connection->select('section_id, COUNT(*) AS total, COUNT(deleted_at) AS deleted')
            ->from($this->getTable())
            ->where('event_id = %i', $event->id)
            ->groupBy('section_id')
            ->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            /** @var array{section_id: int|string, total: int|string, deleted: int|string} $values */
            $values = $row->toArray();
            $counts[(int)$values['section_id']] = new ProgrammeSectionCountsDTO(
                (int)$values['total'] - (int)$values['deleted'],
                (int)$values['deleted'],
            );
        }

        return $counts;
    }

    /**
     * @return array<int, ProgrammePlaceCountsDTO>
     */
    public function countByPlace(Event $event): array
    {
        /** @var list<Row> $rows */
        $rows = $this->connection->select('place_id, COUNT(*) AS total, COUNT(deleted_at) AS deleted')
            ->from($this->getTable())
            ->where('event_id = %i', $event->id)
            ->where('place_id IS NOT NULL')
            ->groupBy('place_id')
            ->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            /** @var array{place_id: int|string, total: int|string, deleted: int|string} $values */
            $values = $row->toArray();
            $counts[(int)$values['place_id']] = new ProgrammePlaceCountsDTO(
                (int)$values['total'] - (int)$values['deleted'],
                (int)$values['deleted'],
            );
        }

        return $counts;
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
