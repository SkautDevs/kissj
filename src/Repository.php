<?php

namespace kissj;

use LeanMapper\Entity;
use LeanMapper\Fluent;
use LeanMapper\Repository as BaseRepository;

class Repository extends BaseRepository {

	public function find(int $id) {
		return $this->findOneBy(['id' => $id]);
	}

	public function findOneBy(array $criteria) {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
		$row = $qb->fetch();

		if ($row === false) {
			throw new \Exception('Entity was not found.');
		}
		// second part
		return $this->createEntity($row);
	}

	public function countBy(array $criteria): int {
		/** @var Fluent $qb */
		$qb = $this->connection->select('count(*)')->from($this->getTable());
		$this->addConditions($qb, $criteria);
		$row = $qb->fetchSingle();

		return $row;
	}

	protected function addConditions(Fluent $qb, array $criteria) {
		foreach ($criteria as $field => $value) {
			if ($value instanceof Entity) {
				$qb->where($field . "_id = %i", $value->id);
			} else {
				$qb->where("$field = %s", $value);
			}
		}
	}

	public function findIdBy(array $criteria): int {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
		$id = $qb->fetchSingle();

		return $id;
	}

	public function findAll() {
		return $this->createEntities(
			$this->createFluent()
				->fetchAll()
		);
	}

}