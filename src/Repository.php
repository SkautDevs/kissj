<?php

namespace kissj;

use LeanMapper\Repository as BaseRepository;

class Repository extends BaseRepository {

	public function find(int $id) {
		return $this->findOneBy(['id' => $id]);
	}

	public function findOneBy(array $criteria) {
		// first part
		$qb = $this->connection->select('*')
			->from($this->getTable());
		foreach ($criteria as $field => $value) {
			$qb->where("$field = %i", $value);
		}
		$row = $qb->fetch();

		if ($row === false) {
			throw new \Exception('Entity was not found.');
		}
		// second part
		return $this->createEntity($row);
	}

	public function findIdBy(array $criteria): int {
		$qb = $this->connection->select('id')
			->from($this->getTable());
		foreach ($criteria as $field => $value) {
			$qb->where("$field = %i", $value);
		}
		$id = $qb->fetchSingle();

		return $id;
	}

//	public function findBy(array $criteria): array {
//		// first part
//		$row = $this->connection->select('*')
//			->from($this->getTable())
//			->where('id = %i', $id)
//			->fetch();
//
//		if ($row === false) {
//			throw new \Exception('Entity was not found.');
//		}
//		// second part
//		return $this->createEntity($row);
//	}

	public function findAll() {
		return $this->createEntities(
			$this->connection->select('*')
				->from($this->getTable())
				->fetchAll()
		);
	}

}