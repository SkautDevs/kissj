<?php

declare(strict_types=1);

namespace kissj\Programme\Place;

use Dibi\ForeignKeyConstraintViolationException;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\EntityNotFoundException;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Programme\DTO\ProgrammePlaceCountsDTO;

/**
 * @table programme_place
 *
 * @method ProgrammePlace get(int $placeId)
 * @method ProgrammePlace getOneBy(mixed[] $criteria)
 * @method ProgrammePlace[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammePlace|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class ProgrammePlaceRepository extends Repository
{
    /**
     * @return list<ProgrammePlace>
     */
    public function findForEvent(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->where('event_id = %i', $event->id);
        $qb->orderBy('name')->asc();
        $qb->orderBy('id')->asc();

        /** @var list<Row> $rows */
        $rows = $qb->fetchAll();

        /** @var array<int, ProgrammePlace> $places */
        $places = $this->createEntities($rows);

        return array_values($places);
    }

    public function findForEventById(int $placeId, Event $event): ?ProgrammePlace
    {
        return $this->findOneBy(['id' => $placeId, 'event' => $event]);
    }

    public function getForEventById(int $placeId, Event $event): ProgrammePlace
    {
        $place = $this->findForEventById($placeId, $event);
        if ($place === null) {
            throw new EntityNotFoundException('Programme place not found for event');
        }

        return $place;
    }

    public function findForEventByName(Event $event, string $name): ?ProgrammePlace
    {
        return $this->findOneBy(['event' => $event, 'name' => $name]);
    }

    public function create(Event $event, string $name, ?Coordinates $coordinates): ProgrammePlace
    {
        $place = new ProgrammePlace();
        $place->event = $event;
        $place->name = $name;
        $place->lat = $coordinates?->lat;
        $place->lng = $coordinates?->lng;
        $this->persist($place);

        return $place;
    }

    public function countProgrammes(ProgrammePlace $place): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(*)')
            ->from('programme')
            ->where('place_id = %i', $place->id)
            ->fetchSingle();

        return $count;
    }

    /**
     * @return array<int, ProgrammePlaceCountsDTO>
     */
    public function countProgrammesByPlace(Event $event): array
    {
        /** @var list<Row> $rows */
        $rows = $this->connection->select('place_id, COUNT(*) AS total, COUNT(deleted_at) AS deleted')
            ->from('programme')
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

    public function deleteUnlessReferenced(ProgrammePlace $place): bool
    {
        try {
            $this->connection->delete($this->getTable())->where('id = %i', $place->id)->execute();
        } catch (ForeignKeyConstraintViolationException) {
            return false;
        }

        return true;
    }
}
