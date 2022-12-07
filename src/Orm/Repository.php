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
     * @return bool
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
     * @param array<string,bool> $orderBy
     * @return Entity
     */
    public function getOneBy(array $criteria, array $orderBy = []): Entity
    {
        $entity = $this->findOneBy($criteria, $orderBy);
        if ($entity === null) {
            throw new \RuntimeException('Entity was not found.');
        }

        return $entity;
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @param array<string,bool> $orderBy
     * @return Entity[]
     */
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

    /**
     * @param array<array<string,Entity|Relation|bool|int|float|string>> $criterias
     * @param array<string,bool> $orderBy
     * @return Entity[]
     */
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

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @param array<string,bool> $orderBy
     * @return Entity|null
     */
    public function findOneBy(array $criteria, array $orderBy = []): ?Entity
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        $this->addOrderBy($qb, $orderBy);
        /** @var ?Row $row */
        $row = $qb->fetch();

        if ($row === null) {
            return null;
        }

        return $this->createEntity($row);
    }

    /**
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @return int
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
     * @param Fluent $qb
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @return void
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
     * @param array<string,Entity|Relation|bool|int|float|string> $criteria
     * @return int
     */
    public function findIdBy(array $criteria): int
    {
        $qb = $this->createFluent();
        $this->addConditions($qb, $criteria);
        /** @var int $id */
        $id = $qb->fetchSingle();

        return $id;
    }

    /**
     * @return Entity[]
     */
    public function findAll(): array
    {
        return $this->createEntities($this->createFluent()->fetchAll());
    }

    /**
     * @param Fluent $qb
     * @param array<string,bool> $orderBy
     * @return void
     */
    protected function addOrderBy(Fluent $qb, array $orderBy): void
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
