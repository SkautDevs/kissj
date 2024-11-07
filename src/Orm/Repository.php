<?php

declare(strict_types=1);

namespace kissj\Orm;

use Dibi\Row;
use LeanMapper\Entity;
use LeanMapper\Fluent;
use LeanMapper\Repository as BaseRepository;

/**
 * @property string[] $onBeforeCreate
 * @property string[] $onBeforePersist
 */
class Repository extends BaseRepository
{
    public function initEvents(): void
    {
        $this->onBeforeCreate[] = EntityDatetime::class . '::setCreatedAtBeforeCreate';
        $this->onBeforePersist[] = EntityDatetime::class . '::setUpdatedAtBeforePersist';
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     */
    public function isExisting(array $criteria): bool
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $row = $qb->fetch();

        return $row !== null;
    }

    public function get(int $id): Entity
    {
        return $this->getOneBy(['id' => $id]);
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     */
    public function getOneBy(array $criteria): Entity
    {
        $entity = $this->findOneBy($criteria);
        if ($entity === null) {
            throw new \RuntimeException('Entity was not found.');
        }

        return $entity;
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @param Order[] $orders
     * @return Entity[]
     */
    public function findBy(array $criteria, array $orders = []): array
    {
        $qb = $this->createFluent();

        $this->addConditions($qb, $criteria);
        $this->addOrdersBy($qb, $orders);

        //      this little boi dumps sql query
        //		$qb->getConnection()->test($qb->_export(null, ['%ofs %lmt', null, null]));

        $rows = $qb->fetchAll();
        $entities = [];

        foreach ($rows as $row) {
            $entities[] = $this->createEntity($row);
        }

        return $entities;
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @param Order[] $orders
     */
    public function findOneBy(array $criteria, array $orders = []): ?Entity
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $this->addOrdersBy($qb, $orders);

        /** @var ?Row $row */
        $row = $qb->fetch();

        if ($row === null) {
            return null;
        }

        return $this->createEntity($row);
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     */
    public function countBy(array $criteria): int
    {
        $qb = $this->connection->select('count(*)')->from($this->getTable());
        $this->addConditions($qb, $criteria);
        /** @var int $row */
        $row = $qb->fetchSingle();

        return $row;
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     */
    protected function addConditions(Fluent $qb, array $criteria): void
    {
        foreach ($criteria as $field => $value) {
            if ($value instanceof Entity) {
                $columnName = $this->mapper->getRelationshipColumn(
                    $this->table,
                    $this->mapper->getTable($value::class)
                );
                $qb->where("$columnName = %i", $value->id);
            } elseif ($value instanceof Relation) {
                if ($value->relation === 'IN') {
                    $qb->where("$field $value->relation %in", $value->value);
                } else {
                    $qb->where("$field $value->relation %s", $value->value);
                }
            } elseif (is_bool($value)) {
                $qb->where("$field = %b", $value);
            } elseif (is_int($value)) {
                $qb->where("$field = %i", $value);
            } elseif (is_float($value)) {
                $qb->where("$field = %f", $value);
            } else {
                $qb->where("$field = %s", $value);
            }
        }
    }

    /**
     * @param Order[] $orders
     */
    protected function addOrdersBy(Fluent $qb, array $orders): void
    {
        foreach ($orders as $order) {
            $qb->orderBy($order->getField());

            if ($order->isOrderAsc()) {
                $qb->asc();
            } else {
                $qb->desc();
            }
        }
    }

    protected function overloadEntityIfNeeded(): ?string
    {
        if ($this->getTable() === 'participant') {
            return static::class;
        }

        return null;
    }
}
