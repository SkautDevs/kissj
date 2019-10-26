<?php

namespace kissj\Orm;

use kissj\Event\Event;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Payment\Payment;
use kissj\User\LoginToken;
use kissj\User\User;
use LeanMapper\Caller;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\IMapper;
use LeanMapper\Row;

class Mapper implements IMapper {
    protected $defaultEntityNamespace = 'kissj';
    protected $relationshipTableGlue = '_';

    public function getPrimaryKey($table): string {
        return 'id';
    }

    public function getTable($entityClass): string {
        $participantVariants = [
            PatrolLeader::class,
            PatrolParticipant::class,
            Ist::class,
            Guest::class,
        ];
        if (in_array($entityClass, $participantVariants, true)) {
            $entityClass = Participant::class;
        }

        return $this->toUnderScore($this->trimNamespace($entityClass));
    }

    public function getEntityClass($table, Row $row = null): string {
        switch ($table) {
            case 'user':
                return User::class;

            case 'logintoken':
                return LoginToken::class;

            case 'payment':
                return Payment::class;

            case 'event':
                return Event::class;

            case 'participant':
                if ($row === null) {
                    return Participant::class;
                }
                switch ($row->getData()['role']) {
                    case User::ROLE_PATROL_LEADER:
                        return PatrolLeader::class;

                    case User::ROLE_PATROL_PARTICIPANT:
                        return PatrolParticipant::class;

                    case User::ROLE_IST:
                        return Ist::class;

                    case User::ROLE_GUEST:
                        return Guest::class;

                    default:
                        throw new \UnexpectedValueException('Got unknown Participant role: '.$row->getData()['role']);
                }

            default:
                throw new \UnexpectedValueException('Got unknown table name: '.$table);
        }
    }

    public function getColumn($entityClass, $field): string {
        return $this->toUnderScore($field);
    }

    public function getEntityField($table, $column): string {
        return $this->toCamelCase($column);
    }

    public function getRelationshipTable($sourceTable, $targetTable): string {
        return $sourceTable.$this->relationshipTableGlue.$targetTable;
    }

    public function getRelationshipColumn($sourceTable, $targetTable): string {
        return $targetTable.'_'.$this->getPrimaryKey($targetTable);
    }

    public function getTableByRepositoryClass($repositoryClass): string {
        $matches = [];
        if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
            return strtolower($matches[1]);
        }
        throw new InvalidStateException('Cannot determine table name.');
    }

    public function getImplicitFilters($entityClass, Caller $caller = null) {
        return [];
    }

    protected function trimNamespace($class): string {
        $class = explode('\\', $class);

        return end($class);
    }

    protected function toUnderScore(string $string): string {
        return lcfirst(preg_replace_callback('#(?<=.)([A-Z])#', function ($m) {
            return '_'.strtolower($m[1]);
        }, $string));
    }

    protected function toCamelCase(string $string): string {
        return preg_replace_callback('#_(.)#', function ($m) {
            return strtoupper($m[1]);
        }, $string);
    }
}
