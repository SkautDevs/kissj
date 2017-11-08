<?php

namespace kissj;

use Dibi\Fluent;
use LeanMapper\Entity;
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

	public function countOneBy(array $criteria) {
		$qb = $this->createFluent();
		$this->addConditions($qb, $criteria);
		$row = $qb->fetch();

		if ($row === false) {
			throw new \Exception('Entity was not found.');
		}
		// second part
		return $this->createEntity($row);
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