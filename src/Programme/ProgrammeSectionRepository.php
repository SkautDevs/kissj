<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\ForeignKeyConstraintViolationException;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Programme\DTO\ProgrammeSectionCountsDTO;
use kissj\User\UserStatus;
use RuntimeException;

/**
 * @table programme_section
 *
 * @method ProgrammeSection get(int $sectionId)
 * @method ProgrammeSection getOneBy(mixed[] $criteria)
 * @method ProgrammeSection[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammeSection|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class ProgrammeSectionRepository extends Repository
{
    /**
     * @return list<ProgrammeSection>
     */
    public function findForEvent(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->where('event_id = %i', $event->id);
        $qb->orderBy('sort_order')->asc();
        $qb->orderBy('id')->asc();

        /** @var list<Row> $rows */
        $rows = $qb->fetchAll();

        /** @var array<int, ProgrammeSection> $sections */
        $sections = $this->createEntities($rows);

        return array_values($sections);
    }

    public function getForEventById(int $sectionId, Event $event): ProgrammeSection
    {
        $section = $this->findOneBy(['id' => $sectionId, 'event' => $event]);
        if ($section === null) {
            throw new RuntimeException('Programme section not found for event');
        }

        return $section;
    }

    public function getNextSortOrder(Event $event): int
    {
        /** @var int $nextSortOrder */
        $nextSortOrder = $this->connection->select('COALESCE(MAX(sort_order), -1) + 1')
            ->from($this->getTable())
            ->where('event_id = %i', $event->id)
            ->fetchSingle();

        return $nextSortOrder;
    }

    public function countProgrammes(ProgrammeSection $section): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(*)')
            ->from('programme')
            ->where('section_id = %i', $section->id)
            ->fetchSingle();

        return $count;
    }

    public function deleteUnlessReferenced(ProgrammeSection $section): bool
    {
        try {
            $this->connection->delete($this->getTable())->where('id = %i', $section->id)->execute();
        } catch (ForeignKeyConstraintViolationException) {
            return false;
        }

        return true;
    }

    public function countParticipantsWithOverlappingRegistrations(ProgrammeSection $section): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(DISTINCT pr1.participant_id)')
            ->from('programme_registration')->as('pr1')
            ->join('programme')->as('p1')->on('p1.id = pr1.programme_id')
            ->join('programme_registration')->as('pr2')
            ->on('pr2.participant_id = pr1.participant_id AND pr2.id <> pr1.id')
            ->join('programme')->as('p2')->on('p2.id = pr2.programme_id')
            ->join('participant')->as('pa')->on('pa.id = pr1.participant_id')
            ->leftJoin('participant')->as('pl')->on('pl.id = pa.patrol_leader_id')
            ->join('user')->as('u')->on('u.id = COALESCE(pa.user_id, pl.user_id)')
            ->where('p1.section_id = %i', $section->id)
            ->where('p2.section_id = %i', $section->id)
            ->where('pr1.deleted_at IS NULL')
            ->where('pr2.deleted_at IS NULL')
            ->where('p1.deleted_at IS NULL')
            ->where('p2.deleted_at IS NULL')
            ->where('u.status != %s', UserStatus::Cancelled->value)
            ->where('p1.start < [p2.end]')
            ->where('p2.start < [p1.end]')
            ->fetchSingle();

        return $count;
    }

    /**
     * @return array<int, ProgrammeSectionCountsDTO>
     */
    public function countProgrammesBySection(Event $event): array
    {
        /** @var list<Row> $rows */
        $rows = $this->connection->select('section_id, COUNT(*) AS total, COUNT(deleted_at) AS deleted')
            ->from('programme')
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
}
