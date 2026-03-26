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
     * @param list<Order> $orders
     * @return list<Entity>
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
            /** @var Row&iterable<string, mixed> $row */
            $entities[] = $this->createEntity($row);
        }

        return $entities;
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @param list<Order> $orders
     */
    public function findOneBy(array $criteria, array $orders = []): ?Entity
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $this->addOrdersBy($qb, $orders);

        /** @var ?(Row&iterable<string, mixed>) $row */
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
            } elseif (is_string($value)) {
                // remove invalid UTF-8 characters to sanitize malicious inputs
                $qb->where("$field = %s", mb_convert_encoding($value, 'UTF-8', 'UTF-8'));
            } else {
                $qb->where("$field = %s", $value);
            }
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transactional(callable $callback): mixed
    {
        $this->connection->begin();
        try {
            $result = $callback();
            $this->connection->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->connection->rollback();
            throw $e;
        }
    }

    /**
     * @param list<Order> $orders
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
}
