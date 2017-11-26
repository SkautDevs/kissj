<?php

namespace kissj\Orm;

use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\User\LoginToken;
use kissj\User\Role;
use kissj\User\User;
use LeanMapper\Caller;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\IMapper;
use LeanMapper\Row;

class Mapper implements IMapper {

	/** @var string */
	protected $defaultEntityNamespace = 'kissj';

	/** @var string */
	protected $relationshipTableGlue = '_';

	public function getPrimaryKey($table) {
		return 'id';
	}

	public function getTable($entityClass) {
		return strtolower($this->trimNamespace($entityClass));
	}

	public function getEntityClass($table, Row $row = null) {
		if ($table === 'user') {
			return User::class;
		}
		if ($table === 'logintoken') {
			return LoginToken::class;
		}
		if ($table === 'patrolparticipant') {
			return PatrolParticipant::class;
		}
		if ($table === 'patrolleader') {
			return PatrolLeader::class;
		}
		if ($table === 'role') {
			return Role::class;
		}
		return ($this->defaultEntityNamespace !== null ? $this->defaultEntityNamespace . '\\' : '') . ucfirst($table);
	}

	public function getColumn($entityClass, $field) {
		return $field;
	}

	public function getEntityField($table, $column) {
		return $column;
	}

	public function getRelationshipTable($sourceTable, $targetTable) {
		return $sourceTable . $this->relationshipTableGlue . $targetTable;
	}

	public function getRelationshipColumn($sourceTable, $targetTable) {
		return $targetTable . ucfirst($this->getPrimaryKey($targetTable));
	}

	public function getTableByRepositoryClass($repositoryClass) {
		$matches = [];
		if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
			return strtolower($matches[1]);
		}
		throw new InvalidStateException('Cannot determine table name.');
	}

	public function getImplicitFilters($entityClass, Caller $caller = null) {
		return [];
	}

	/**
	 * Trims namespace part from fully qualified class name
	 *
	 * @param $class
	 * @return string
	 */
	protected function trimNamespace($class) {
		$class = explode('\\', $class);
		return end($class);
	}

}
