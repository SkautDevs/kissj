<?php

namespace kissj\Orm;

use LeanMapper\Entity;
use LeanMapper\Fluent;
use LeanMapper\Repository as BaseRepository;

/**
 * @property array onBeforeCreate
 * @property array onBeforePersist
 */
class Repository extends BaseRepository
{
    public function initEvents(): void
    {
        $this->onBeforeCreate[] = EntityDatetime::class . '::setCreatedAtBeforeCreate';
        $this->onBeforePersist[] = EntityDatetime::class . '::setUpdatedAtBeforePersist';
    }

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

    public function getOneBy(array $criteria, array $orderBy = []): Entity
    {
        $entity = $this->findOneBy($criteria);
        if ($entity === null) {
            throw new \RuntimeException('Entity was not found.');
        }

        return $entity;
    }

    public function findBy(array $criteria, array $orderBy = []): array
    {
        $qb = $this->createFluent();

        $this->addConditions($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);

        //      this little boi dumps sql query
        //		$qb->getConnection()->test($qb->_export(null, ['%ofs %lmt', null, null]));

        $rows = $qb->fetchAll();
        $entities = [];

        foreach ($rows as $row) {
            $entities[] = $this->createEntity($row);
        }

        return $entities;
    }

    public function findByMultiple(array $criterias, array $orderBy = []): array
    {
        $qb = $this->createFluent();

        foreach ($criterias as $criterium) {
            $this->addConditions($qb, $criterium);
        }
        $this->addOrderBy($qb, $orderBy);

        $rows = $qb->fetchAll();
        $entities = [];

        foreach ($rows as $row) {
            $entities[] = $this->createEntity($row);
        }

        return $entities;
    }

    public function findOneBy(array $criteria, array $orderBy = []): ?Entity
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);
        $row = $qb->fetch();

        if ($row === false) {
            return null;
        }

        return $this->createEntity($row);
    }

    public function countBy(array $criteria): int
    {
        /** @var Fluent $qb */
        $qb = $this->connection->select('count(*)')->from($this->getTable());
        $this->addConditions($qb, $criteria);
        $row = $qb->fetchSingle();

        return $row;
    }

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

    public function findIdBy(array $criteria): int
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $id = $qb->fetchSingle();

        return $id;
    }

    public function findAll()
    {
        return $this->createEntities(
            $this->createFluent()
                ->fetchAll()
        );
    }

    protected function addOrderBy(Fluent $qb, array $orderBy)
    {
        foreach ($orderBy as $order => $asc) {
            if ($asc) {
                $qb->orderBy($order)->asc();
            } else {
                $qb->orderBy($order)->desc();
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
