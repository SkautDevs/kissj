<?php

declare(strict_types=1);

namespace kissj\Orm;

use kissj\BankPayment\BankPayment;
use kissj\Deal\Deal;
use kissj\Event\Event;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Payment\Payment;
use kissj\User\LoginToken;
use kissj\User\User;
use LeanMapper\Caller;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\IMapper;
use LeanMapper\Row;

class Mapper implements IMapper
{
    protected string $defaultEntityNamespace = 'kissj';
    protected string $relationshipTableGlue = '_';

    public function getPrimaryKey(string $table): string
    {
        return 'id';
    }

    public function getTable(string $entityClass): string
    {
        $participantVariants = [
            PatrolLeader::class,
            PatrolParticipant::class,
            TroopLeader::class,
            TroopParticipant::class,
            Ist::class,
            Guest::class,
        ];
        if (in_array($entityClass, $participantVariants, true)) {
            $entityClass = Participant::class;
        }

        return $this->toUnderScore($this->trimNamespace($entityClass));
    }

    public function getEntityClass(string $table, ?Row $row = null): string
    {
        switch ($table) {
            case 'user':
                return User::class;

            case 'logintoken':
                return LoginToken::class;

            case 'payment':
                return Payment::class;

            case 'bankpayment':
                return BankPayment::class;

            case 'event':
                return Event::class;

            case 'participant':
                if ($row === null) {
                    return Participant::class;
                }

                return match ($row->getData()['role']) {
                    ParticipantRole::PatrolLeader->value => PatrolLeader::class,
                    ParticipantRole::PatrolParticipant->value => PatrolParticipant::class,
                    ParticipantRole::TroopLeader->value => TroopLeader::class,
                    ParticipantRole::TroopParticipant->value => TroopParticipant::class,
                    ParticipantRole::Ist->value => Ist::class,
                    ParticipantRole::Guest->value => Guest::class,
                    default => throw new \UnexpectedValueException('Got unknown Participant role: '
                        . $row->getData()['role'] . ' for Participant with ID: ' . $row->getData()['id']),
                };

            case 'deal':
                return Deal::class;

            default:
                throw new \UnexpectedValueException('Got unknown table name: ' . $table);
        }
    }

    public function getColumn(string $entityClass, string $field): string
    {
        return $this->toUnderScore($field);
    }

    public function getEntityField(string $table, string $column): string
    {
        return $this->toCamelCase($column);
    }

    public function getRelationshipTable(string $sourceTable, string $targetTable): string
    {
        return $sourceTable . $this->relationshipTableGlue . $targetTable;
    }

    public function getRelationshipColumn(
        string $sourceTable,
        string $targetTable,
        ?string $relationshipName = null
    ): string {
        return $targetTable . '_' . $this->getPrimaryKey($targetTable);
    }

    public function getTableByRepositoryClass(string $repositoryClass): string
    {
        $matches = [];
        if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
            return strtolower((string) $matches[1]);
        }
        throw new InvalidStateException('Cannot determine table name.');
    }

    /**
     * @param string $entityClass
     * @param ?Caller $caller
     * @return string[]
     */
    public function getImplicitFilters(string $entityClass, Caller $caller = null): array
    {
        return [];
    }

    protected function trimNamespace(string $class): string
    {
        $pieces = explode('\\', $class);

        return end($pieces);
    }

    protected function toUnderScore(string $string): string
    {
        $pregReplaced = preg_replace_callback('#(?<=.)([A-Z])#', fn ($m) => '_' . strtolower($m[1]), $string);
        if ($pregReplaced === null) {
            throw new \RuntimeException('preg_replace_callback failed');
        }

        return lcfirst($pregReplaced);
    }

    protected function toCamelCase(string $string): string
    {
        $pregReplaced = preg_replace_callback('#_(.)#', fn ($m) => strtoupper($m[1]), $string);
        if ($pregReplaced === null) {
            throw new \RuntimeException('preg_replace_callback failed');
        }

        return $pregReplaced;
    }

    /**
     * @param string $table
     * @param mixed[] $values
     * @return mixed[]
     */
    public function convertToRowData(string $table, array $values): array
    {
        return $values;
    }

    /**
     * @param string $table
     * @param mixed[] $data
     * @return mixed[]
     */
    public function convertFromRowData(string $table, array $data): array
    {
        return $data;
    }
}
