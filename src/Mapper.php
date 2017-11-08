<?php

namespace kissj;

use kissj\User\User;
use LeanMapper\Caller;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\IMapper;
use LeanMapper\Row;

/**
 * Default IMapper implementation
 *
 * @author Vojtěch Kohout
 */
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
		return $targetTable . '_' . $this->getPrimaryKey($targetTable);
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
