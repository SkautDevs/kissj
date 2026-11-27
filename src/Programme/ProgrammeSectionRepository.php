<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\ForeignKeyConstraintViolationException;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\EntityNotFoundException;
use kissj\Orm\Order;
use kissj\Orm\Repository;

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
            throw new EntityNotFoundException('Programme section not found for event');
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

    public function deleteUnlessReferenced(ProgrammeSection $section): bool
    {
        try {
            $this->connection->delete($this->getTable())->where('id = %i', $section->id)->execute();
        } catch (ForeignKeyConstraintViolationException) {
            return false;
        }

        return true;
    }
}
